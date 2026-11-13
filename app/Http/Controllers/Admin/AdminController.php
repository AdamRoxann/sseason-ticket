<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'users' => User::count(),
            'tickets' => Ticket::count(),
            'issued' => Ticket::where('status', 'issued')->count(),
            'checked_in' => Ticket::where('status', 'checked_in')->count(),
        ];

        $recentTickets = Ticket::with(['user', 'category', 'event'])->latest()->limit(10)->get();

        return view('admin.dashboard', compact('stats', 'recentTickets'));
    }

    public function users(Request $request)
    {
        $users = User::withCount('tickets')
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = $request->string('q')->toString();
                $query->where(fn ($q) => $q->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%"));
            })
            ->latest()->paginate(25)->withQueryString();

        return view('admin.users', compact('users'));
    }

    public function tickets(Request $request)
    {
        $tickets = Ticket::with(['user', 'event', 'category'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = $request->string('q')->toString();
                $query->where(fn ($q) => $q->where('public_code', 'like', "%{$term}%")
                    ->orWhereHas('user', fn ($user) => $user->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%")));
            })
            ->latest()->paginate(25)->withQueryString();

        return view('admin.tickets', compact('tickets'));
    }

    public function events()
    {
        $events = Event::withCount(['categories', 'tickets'])->latest()->paginate(20);
        return view('admin.events.index', compact('events'));
    }

    public function createEvent()
    {
        return view('admin.events.form', ['event' => new Event(), 'categories' => collect()]);
    }

    public function storeEvent(Request $request)
    {
        $data = $this->validateEvent($request);
        $data['is_active'] = $request->boolean('is_active');
        if ($request->hasFile('floor_plan')) {
            $data['floor_plan_path'] = $request->file('floor_plan')->store('floor-plans', 'public');
        }
        $event = Event::create($data);
        if ($event->is_active) {
            Event::where('id', '!=', $event->id)->update(['is_active' => false]);
        }

        return redirect()->route('admin.events.edit', $event)->with('success', 'Event created. You can now configure its ticket tiers.');
    }

    public function editEvent(Event $event)
    {
        $event->load('categories');
        return view('admin.events.form', ['event' => $event, 'categories' => $event->categories()->orderBy('id')->get()]);
    }

    public function updateEvent(Request $request, Event $event)
    {
        $data = $this->validateEvent($request);
        $data['is_active'] = $request->boolean('is_active');
        if ($request->hasFile('floor_plan')) {
            if ($event->floor_plan_path) Storage::disk('public')->delete($event->floor_plan_path);
            $data['floor_plan_path'] = $request->file('floor_plan')->store('floor-plans', 'public');
        }
        $event->update($data);
        if ($event->is_active) {
            Event::where('id', '!=', $event->id)->update(['is_active' => false]);
        }

        return back()->with('success', 'Event settings updated.');
    }

    public function destroyEvent(Event $event)
    {
        if ($event->tickets()->exists() || $event->queueEntries()->exists()) {
            return back()->withErrors(['event' => 'This event has tickets or queue records and cannot be deleted. Deactivate it instead.']);
        }
        if ($event->floor_plan_path) Storage::disk('public')->delete($event->floor_plan_path);
        $event->delete();
        return redirect()->route('admin.events')->with('success', 'Event deleted.');
    }

    public function storeCategory(Request $request, Event $event)
    {
        $data = $this->validateCategory($request, $event);
        $data['is_active'] = $request->boolean('is_active');
        $data['sold'] = 0;
        $event->categories()->create($data);
        return back()->with('success', 'Ticket tier created.');
    }

    public function updateCategory(Request $request, Event $event, TicketCategory $category)
    {
        abort_unless($category->event_id === $event->id, 404);
        $data = $this->validateCategory($request, $event, $category);
        $data['is_active'] = $request->boolean('is_active');
        // Never let an edit reduce quota below tickets already issued.
        if ((int) $data['quota'] < $category->sold) {
            return back()->withErrors(['quota' => "Quota cannot be lower than {$category->sold} tickets already sold."])->withInput();
        }
        $category->update($data);
        return back()->with('success', 'Ticket tier updated.');
    }

    public function destroyCategory(Event $event, TicketCategory $category)
    {
        abort_unless($category->event_id === $event->id, 404);
        if ($category->sold > 0 || $category->tickets()->exists()) {
            return back()->withErrors(['category' => 'This tier already has tickets. Deactivate it instead of deleting it.']);
        }
        $category->delete();
        return back()->with('success', 'Ticket tier deleted.');
    }

    private function validateEvent(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'venue' => ['nullable', 'string', 'max:255'],
            'event_starts_at' => ['nullable', 'date'],
            'registration_opens_at' => ['nullable', 'date'],
            'registration_closes_at' => ['nullable', 'date', 'after_or_equal:registration_opens_at'],
            'floor_plan' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);
    }

    private function validateCategory(Request $request, Event $event, ?TicketCategory $category = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('ticket_categories')->where('event_id', $event->id)->ignore($category?->id)],
            'quota' => ['required', 'integer', 'min:1', 'max:1000000'],
            'max_per_order' => ['required', 'integer', 'in:1'],
            'display_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);
    }
}
