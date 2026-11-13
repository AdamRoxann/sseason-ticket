@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-xl py-10 sm:py-12">
    <div class="rounded-3xl bg-cream p-6 text-olive shadow-2xl sm:p-8">
        <p class="text-xs uppercase tracking-[.25em]">Sheila Saukia presents</p>
        <h1 class="mt-3 font-display text-4xl">A SSeason to Bloom</h1>
        <div class="my-6 border-t border-olive/20"></div>
        <p class="text-sm">{{ $ticket->event->venue }}</p>
        <p class="mt-1 text-sm">{{ $ticket->event->event_starts_at?->format('d F Y') ?? 'Event schedule: To Be Announced (WIB)' }}</p>
        <div class="my-8 flex justify-center">
            @if($ticket->qr_path)
                <img class="h-56 w-56" src="{{ Storage::disk('public')->url($ticket->qr_path) }}" alt="Ticket QR code">
            @else
                <p>QR code is being prepared.</p>
            @endif
        </div>
        <div class="rounded-2xl border border-olive/15 p-4 text-center">
            <p class="text-xs uppercase tracking-widest text-olive/60">Your ticket tier</p>
            <p class="mt-1 font-display text-3xl">{{ $ticket->category->name }}</p>
            <p class="mt-2 text-xs">Show this tier at the event entrance.</p>
        </div>
        <p class="mt-5 break-all text-center font-mono text-xs">{{ $ticket->public_code }}</p>
        <p class="mt-3 text-center text-sm">{{ auth()->user()->name }}</p>
        <p class="mt-4 text-center text-xs uppercase tracking-wider">Status: {{ str_replace('_', ' ', $ticket->status) }}</p>
    </div>
    <p class="mt-5 text-center text-xs text-cream/50">Present this QR code at the registration desk. Staff will verify your tier and check in your ticket.</p>
</div>
@endsection
