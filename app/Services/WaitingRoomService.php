<?php
namespace App\Services;

use App\Events\QueueStatusUpdated;
use App\Models\Event;
use App\Models\WaitingRoomEntry;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class WaitingRoomService
{
    private function key(Event $event): string { return 'ticket-war:event:'.$event->id.':queue'; }

    public function join(Event $event, int $userId): WaitingRoomEntry
    {
        $existing = WaitingRoomEntry::where('event_id', $event->id)->where('user_id', $userId)->first();
        if ($existing) {
            if (in_array($existing->status, ['waiting','admitted'], true)) return $existing;
            throw ValidationException::withMessages(['queue' => 'Akun ini sudah pernah mengikuti ticket war untuk event ini.']);
        }
        if (!$event->registration_opens_at) {
            throw ValidationException::withMessages(['queue' => 'Jadwal ticket war belum dikonfigurasi.']);
        }
        if ($event->registration_opens_at && now()->lt($event->registration_opens_at)) {
            throw ValidationException::withMessages(['queue' => 'Ticket war belum dibuka.']);
        }
        if ($event->registration_closes_at && now()->gt($event->registration_closes_at)) {
            throw ValidationException::withMessages(['queue' => 'Periode ticket war sudah berakhir.']);
        }
        $entry = WaitingRoomEntry::create([
            'event_id'=>$event->id, 'user_id'=>$userId, 'queue_token'=>(string) Str::uuid(),
            'status'=>'waiting', 'joined_at'=>now(),
        ]);
        Redis::zadd($this->key($event), (float) now()->format('U.u'), (string) $entry->id);
        $this->admitNext($event);
        return $entry->fresh();
    }

    public function admitNext(Event $event): void
    {
        $lock = Cache::lock('ticket-war:event:'.$event->id.':admission-lock', 5);
        $lock->block(2, function () use ($event) {
            $limit = max(1, (int) config('ticketwar.active_sessions', 100));
            $active = WaitingRoomEntry::where('event_id',$event->id)->where('status','admitted')->where('expires_at','>',now())->count();
            $slots = max(0, $limit - $active);
            if ($slots === 0) return;
            $ids = Redis::zrange($this->key($event), 0, $slots - 1);
            foreach ($ids as $id) {
                $entry = WaitingRoomEntry::where('event_id',$event->id)->whereKey((int)$id)->where('status','waiting')->first();
                Redis::zrem($this->key($event), $id);
                if (!$entry) continue;
                $entry->update(['status'=>'admitted','admitted_at'=>now(),'expires_at'=>now()->addMinutes((int)config('ticketwar.access_minutes',5))]);
                QueueStatusUpdated::dispatch($entry->user_id, ['status'=>'admitted','message'=>'Giliran Anda sudah tiba. Silakan pilih tiket.']);
            }
        });
    }

    public function status(WaitingRoomEntry $entry): array
    {
        $this->admitNext($entry->event);
        $entry->refresh();
        if ($entry->status === 'admitted' && $entry->expires_at?->isPast()) {
            $entry->update(['status'=>'expired']);
        }
        $rank = Redis::zrank($this->key($entry->event), (string)$entry->id);
        $position = $rank === null ? null : ((int)$rank + 1);
        $payload = ['status'=>$entry->status,'position'=>$position,'expires_at'=>$entry->expires_at?->toIso8601String()];
        return $payload;
    }
}
