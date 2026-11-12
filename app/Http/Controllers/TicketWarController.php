<?php
namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\WaitingRoomEntry;
use App\Services\WaitingRoomService;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TicketWarController extends Controller
{
    private function event(): Event
    {
        return Event::where('is_active', true)->with('categories')->firstOrFail();
    }

    public function home() { return view('home', ['event'=>Event::where('is_active',true)->first()]); }
    public function show() { $event = $this->event(); return view('tickets.war', compact('event')); }

    public function enter(Request $request, WaitingRoomService $queue)
    {
        $entry = $queue->join($this->event(), $request->user()->id);
        return redirect()->route('waiting-room.show', $entry);
    }

    public function waitingRoom(WaitingRoomEntry $entry, WaitingRoomService $queue)
    {
        abort_unless($entry->user_id === auth()->id(), 404);
        $status = $queue->status($entry);
        if (request()->expectsJson()) return response()->json($status);
        return view('tickets.waiting-room', compact('entry','status'));
    }

    public function status(WaitingRoomEntry $entry, WaitingRoomService $queue)
    {
        abort_unless($entry->user_id === auth()->id(), 404);
        return response()->json($queue->status($entry));
    }

    public function checkoutForm(WaitingRoomEntry $entry)
    {
        abort_unless($entry->user_id === auth()->id(), 404);
        abort_unless($entry->status === 'admitted' && $entry->expires_at && $entry->expires_at->isFuture(), 403);
        $categories = $entry->event->categories()->where('is_active', true)->get();
        return view('tickets.checkout', compact('entry', 'categories'));
    }

    public function checkout(Request $request, WaitingRoomEntry $entry)
    {
        abort_unless($entry->user_id === $request->user()->id, 404);
        $data = $request->validate(['ticket_category_id'=>['required','integer','exists:ticket_categories,id'],'quantity'=>['required','integer','min:1','max:'.(int)config('ticketwar.max_per_user',2)]]);
        if ($entry->status !== 'admitted' || !$entry->expires_at || $entry->expires_at->isPast()) {
            throw ValidationException::withMessages(['queue'=>'Waktu akses sudah berakhir. Silakan masuk antrean kembali.']);
        }

        $tickets = DB::transaction(function () use ($entry, $data) {
            $category = TicketCategory::whereKey($data['ticket_category_id'])->lockForUpdate()->firstOrFail();
            if ($category->event_id !== $entry->event_id || !$category->is_active) throw ValidationException::withMessages(['ticket_category_id'=>'Kategori tiket tidak tersedia.']);
            if ($category->quota - $category->sold < $data['quantity']) throw ValidationException::withMessages(['quantity'=>'Maaf, kuota tiket sudah habis.']);
            $alreadyOwned = Ticket::where('event_id',$entry->event_id)->where('user_id',$entry->user_id)->count();
            if ($alreadyOwned + $data['quantity'] > min($category->max_per_order, (int)config('ticketwar.max_per_user',2))) throw ValidationException::withMessages(['quantity'=>'Melebihi batas tiket per akun.']);
            $category->increment('sold', $data['quantity']);
            $issued = [];
            for ($i=0; $i < $data['quantity']; $i++) {
                $issued[] = Ticket::create(['event_id'=>$entry->event_id,'ticket_category_id'=>$category->id,'user_id'=>$entry->user_id,'waiting_room_entry_id'=>$entry->id,'public_code'=>(string)Str::uuid(),'status'=>'issued']);
            }
            $entry->update(['status'=>'completed']);
            return $issued;
        }, 3);

        foreach ($tickets as $ticket) {
            $result = (new Builder(writer: new SvgWriter(), data: route('tickets.show', $ticket), size: 300, margin: 10))->build();
            $path = 'ticket-qr/'.$ticket->public_code.'.svg';
            Storage::disk('public')->put($path, $result->getString());
            $ticket->update(['qr_path'=>$path]);
        }
        return redirect()->route('tickets.index')->with('success','Tiket berhasil didapatkan!');
    }

    public function scanPage()
    {
        return view('tickets.scan');
    }

    public function checkIn(Request $request)
    {
        $data = $request->validate([
            'qr_value' => ['required', 'string', 'max:2048'],
        ]);

        // The existing QR payload is the /my-tickets/{ticket-id} URL.
        // Also accept a raw public UUID for manually entered/scanned codes.
        $rawValue = trim($data['qr_value']);
        $path = parse_url($rawValue, PHP_URL_PATH);
        $identifier = $path ? basename(rtrim($path, '/')) : $rawValue;

        $result = DB::transaction(function () use ($identifier) {
            $query = Ticket::query()->lockForUpdate();
            $ticket = ctype_digit($identifier)
                ? $query->whereKey((int) $identifier)->first()
                : $query->where('public_code', $identifier)->first();

            if (!$ticket) {
                return ['state' => 'invalid'];
            }

            if ($ticket->checked_in_at || $ticket->status === 'checked_in') {
                return ['state' => 'already_checked_in', 'ticket_id' => $ticket->id];
            }

            if ($ticket->status !== 'issued') {
                return ['state' => 'invalid_status', 'ticket_id' => $ticket->id];
            }

            $ticket->update([
                'status' => 'checked_in',
                'checked_in_at' => now(),
            ]);

            return ['state' => 'checked_in', 'ticket_id' => $ticket->id];
        }, 3);

        if (empty($result['ticket_id'])) {
            return response()->json([
                'result' => 'invalid',
                'message' => 'QR code tidak valid atau tiket tidak ditemukan.',
            ], 404);
        }

        $ticket = Ticket::with(['event', 'category', 'user'])->findOrFail($result['ticket_id']);
        $ticketDetails = [
            'ticket_code' => $ticket->public_code,
            'attendee' => $ticket->user?->name ?? 'Unknown attendee',
            'event' => $ticket->event?->name,
            'category' => $ticket->category?->name,
            'checked_in_at' => $ticket->checked_in_at?->format('d M Y, H:i:s'),
        ];

        if ($result['state'] === 'already_checked_in') {
            return response()->json([
                'result' => 'already_checked_in',
                'message' => 'Tiket ini sudah pernah digunakan untuk check-in.',
                'ticket' => $ticketDetails,
            ], 409);
        }

        if ($result['state'] === 'invalid_status') {
            return response()->json([
                'result' => 'invalid',
                'message' => 'Status tiket tidak mengizinkan check-in.',
                'ticket' => $ticketDetails,
            ], 422);
        }

        return response()->json([
            'result' => 'checked_in',
            'message' => 'Tiket valid. Check-in berhasil.',
            'ticket' => $ticketDetails,
        ]);
    }

    public function myTickets() { $tickets = Ticket::with(['event','category'])->where('user_id',auth()->id())->latest()->get(); return view('tickets.index',compact('tickets')); }
    public function ticket(Ticket $ticket) { abort_unless($ticket->user_id === auth()->id(),404); $ticket->load(['event','category']); return view('tickets.show',compact('ticket')); }
}
