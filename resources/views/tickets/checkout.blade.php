@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-5xl py-10 sm:py-14">
    <div class="max-w-2xl">
        <p class="text-xs uppercase tracking-[.3em] text-gold">Your turn</p>
        <h1 class="mt-3 font-display text-5xl sm:text-6xl">Choose your ticket tier</h1>
        <p class="mt-4 text-sm leading-6 text-cream/65">
            Select one category based on the event floor plan. Each account can claim one ticket only.
            Your access expires at {{ $entry->expires_at?->timezone('Asia/Jakarta')->format('H:i:s') }} WIB.
        </p>
    </div>

    @if($entry->event->floor_plan_path)
        <section class="mt-8 overflow-hidden rounded-3xl border border-gold/20 bg-black/25 p-3 sm:p-5">
            <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-cream/70">Event floor plan</h2>
            <img src="{{ Storage::disk('public')->url($entry->event->floor_plan_path) }}" alt="Event floor plan reference" class="mx-auto max-h-[620px] w-full rounded-xl bg-white object-contain">
        </section>
    @else
        <section class="mt-8 overflow-hidden rounded-3xl border border-gold/20 bg-black/25 p-3 sm:p-5">
            <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-cream/70">Event floor plan reference</h2>
            <img src="{{ asset('images/floor-plan-reference.jpg') }}" alt="Floor plan reference image" class="mx-auto max-h-[620px] w-full rounded-xl bg-white object-contain">
            <p class="mt-2 text-xs text-cream/45">Reference image only. Ticket tier names and availability are configured by the event administrator.</p>
        </section>
    @endif

    <form method="POST" action="{{ route('waiting-room.checkout', $entry) }}" class="mt-8">
        @csrf
        <fieldset>
            <legend class="text-lg font-semibold">Available ticket categories</legend>
            <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($categories as $category)
                    <label class="relative block cursor-pointer">
                        <input class="peer sr-only" type="radio" name="ticket_category_id" value="{{ $category->id }}" required @checked($loop->first && $category->remaining > 0) @disabled($category->remaining < 1)>
                        <div class="h-full rounded-2xl border border-cream/15 bg-black/20 p-5 transition peer-checked:border-gold peer-checked:bg-gold/10 peer-focus-visible:ring-2 peer-focus-visible:ring-gold {{ $category->remaining < 1 ? 'opacity-45' : 'hover:border-gold/60' }}">
                            <div class="flex items-center justify-between gap-3">
                                <span class="font-display text-3xl">{{ $category->name }}</span>
                                <span class="h-4 w-4 rounded-full border border-current" style="background: {{ $category->display_color ?? '#D7BD72' }}"></span>
                            </div>
                            <p class="mt-3 text-sm text-cream/60">{{ $category->remaining > 0 ? $category->remaining.' tickets remaining' : 'Sold out' }}</p>
                            <p class="mt-4 text-xs uppercase tracking-wider text-gold">Select this tier</p>
                        </div>
                    </label>
                @endforeach
            </div>
        </fieldset>

        @error('ticket_category_id')
            <p class="mt-4 text-sm text-red-300">{{ $message }}</p>
        @enderror
        @error('queue')
            <p class="mt-4 text-sm text-red-300">{{ $message }}</p>
        @enderror

        <div class="mt-8 flex flex-col gap-3 rounded-2xl border border-gold/20 bg-black/20 p-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="font-semibold">Quantity: 1 ticket</p>
                <p class="mt-1 text-xs text-cream/50">One ticket per account. Quantity cannot be changed.</p>
            </div>
            <button class="rounded-full bg-gold px-8 py-3 font-semibold text-olive transition hover:bg-[#ead59b] disabled:opacity-50" @disabled($categories->where('remaining', '>', 0)->isEmpty())>
                Confirm & Get Ticket
            </button>
        </div>
    </form>
</div>
@endsection
