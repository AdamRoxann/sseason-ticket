@extends('layouts.app')
@section('content')
<div class="py-10">
    <p class="text-xs uppercase tracking-[.3em] text-gold">Event configuration</p>
    <div class="mt-3 flex flex-wrap items-center justify-between gap-4">
        <h1 class="font-display text-5xl">Events</h1>
        <a href="{{ route('admin.events.create') }}" class="rounded-full bg-gold px-5 py-3 font-semibold text-olive">Create event</a>
    </div>
    <p class="mt-3 text-sm text-cream/60">Manage event names, schedule, venue, registration window, floor plan and ticket tiers.</p>
    <div class="mt-8 space-y-3">
        @forelse($events as $event)
            <article class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-cream/10 bg-black/20 p-5">
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-xl font-semibold">{{ $event->name }}</h2>
                        @if($event->is_active)<span class="rounded-full bg-emerald-900/60 px-3 py-1 text-xs text-emerald-200">Active</span>@else<span class="rounded-full bg-white/10 px-3 py-1 text-xs text-cream/60">Inactive</span>@endif
                    </div>
                    <p class="mt-1 text-sm text-cream/55">{{ $event->venue ?: 'Venue not set' }} · {{ $event->categories_count }} tiers · {{ $event->tickets_count }} tickets</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('admin.events.edit', $event) }}" class="rounded-full border border-gold/50 px-4 py-2 text-sm text-gold">Manage</a>
                    <form method="POST" action="{{ route('admin.events.destroy', $event) }}" onsubmit="return confirm('Delete this event? Events with tickets or queue records cannot be deleted.')">
                        @csrf @method('DELETE')
                        <button class="rounded-full border border-red-400/40 px-4 py-2 text-sm text-red-200">Delete</button>
                    </form>
                </div>
            </article>
        @empty
            <p class="rounded-2xl border border-cream/10 p-6 text-cream/60">No events configured.</p>
        @endforelse
    </div>
    <div class="mt-6">{{ $events->links() }}</div>
</div>
@endsection
