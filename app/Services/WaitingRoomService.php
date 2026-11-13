<?php

namespace App\Services;

use App\Events\QueueStatusUpdated;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\WaitingRoomEntry;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class WaitingRoomService
{
    private function key(Event $event): string
    {
        return 'ticket-war:event:'.$event->id.':queue';
    }

    public function join(Event $event, int $userId): WaitingRoomEntry
    {
        if (Ticket::where('event_id', $event->id)->where('user_id', $userId)->exists()) {
            throw ValidationException::withMessages([
                'queue' => 'You already have a ticket for this event. Please open My Tickets to view your QR code.',
            ]);
        }

        $existing = WaitingRoomEntry::where('event_id', $event->id)
            ->where('user_id', $userId)->first();

        if ($existing) {
            if (in_array($existing->status, ['waiting', 'admitted'], true)) {
                return $existing;
            }

            throw ValidationException::withMessages([
                'queue' => 'You have already used your ticket-war attempt for this event. Please contact the event team if you need assistance.',
            ]);
        }

        if (!$event->registration_opens_at) {
            throw ValidationException::withMessages(['queue' => 'Ticket registration schedule has not been configured yet.']);
        }

        if (now()->lt($event->registration_opens_at)) {
            throw ValidationException::withMessages(['queue' => 'Ticket war has not opened yet.']);
        }

        if ($event->registration_closes_at && now()->gt($event->registration_closes_at)) {
            throw ValidationException::withMessages(['queue' => 'Ticket registration has closed.']);
        }

        $entry = WaitingRoomEntry::create([
            'event_id' => $event->id,
            'user_id' => $userId,
            'queue_token' => (string) Str::uuid(),
            'status' => 'waiting',
            'joined_at' => now(),
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
            $active = WaitingRoomEntry::where('event_id', $event->id)
                ->where('status', 'admitted')->where('expires_at', '>', now())->count();
            $slots = max(0, $limit - $active);

            if ($slots === 0) {
                return;
            }

            $ids = Redis::zrange($this->key($event), 0, $slots - 1);
            foreach ($ids as $id) {
                $entry = WaitingRoomEntry::where('event_id', $event->id)
                    ->whereKey((int) $id)->where('status', 'waiting')->first();

                Redis::zrem($this->key($event), $id);

                if (!$entry) {
                    continue;
                }

                $entry->update([
                    'status' => 'admitted',
                    'admitted_at' => now(),
                    'expires_at' => now()->addMinutes((int) config('ticketwar.access_minutes', 5)),
                ]);

                QueueStatusUpdated::dispatch($entry->user_id, [
                    'status' => 'admitted',
                    'message' => 'Your turn has arrived. Please choose your ticket tier.',
                ]);
            }
        });
    }

    public function status(WaitingRoomEntry $entry): array
    {
        $this->admitNext($entry->event);
        $entry->refresh();

        if ($entry->status === 'admitted' && $entry->expires_at?->isPast()) {
            $entry->update(['status' => 'expired']);
            $entry->refresh();
        }

        $rank = $entry->status === 'waiting'
            ? Redis::zrank($this->key($entry->event), (string) $entry->id)
            : null;

        return [
            'status' => $entry->status,
            'position' => $rank === null ? null : ((int) $rank + 1),
            'expires_at' => $entry->expires_at?->toIso8601String(),
        ];
    }
}
