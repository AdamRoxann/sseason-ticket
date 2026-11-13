@extends('layouts.app')
@section('content')
<div class="py-10">
    <p class="text-xs uppercase tracking-[.3em] text-gold">Event operations</p>
    <h1 class="mt-3 font-display text-5xl">Admin dashboard</h1>
    <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach(['Registered users' => $stats['users'], 'Tickets issued' => $stats['tickets'], 'Awaiting check-in' => $stats['issued'], 'Checked in' => $stats['checked_in']] as $label => $value)
            <div class="rounded-2xl border border-gold/20 bg-black/25 p-6"><p class="text-sm text-cream/55">{{ $label }}</p><p class="mt-2 font-display text-4xl text-gold">{{ number_format($value) }}</p></div>
        @endforeach
    </div>
    <div class="mt-8 flex flex-wrap gap-3">
        <a class="rounded-full bg-gold px-5 py-3 font-semibold text-olive" href="{{ route('admin.users') }}">View all users</a>
        <a class="rounded-full border border-gold/50 px-5 py-3 font-semibold text-gold" href="{{ route('admin.tickets') }}">View all tickets / QR codes</a>
        <a class="rounded-full border border-gold/50 px-5 py-3 font-semibold text-gold" href="{{ route('admin.scan') }}">Open QR scanner</a>
        <a class="rounded-full border border-gold/50 px-5 py-3 font-semibold text-gold" href="{{ route('admin.events') }}">Manage events & ticket tiers</a>
    </div>
    <h2 class="mt-10 text-xl font-semibold">Recent tickets</h2>
    <div class="mt-4 overflow-x-auto rounded-2xl border border-cream/10">
        <table class="w-full text-left text-sm"><thead class="bg-black/30 text-cream/60"><tr><th class="p-4">Attendee</th><th class="p-4">Tier</th><th class="p-4">Ticket code</th><th class="p-4">Status</th></tr></thead>
        <tbody>@forelse($recentTickets as $ticket)<tr class="border-t border-cream/10"><td class="p-4">{{ $ticket->user?->name }}</td><td class="p-4">{{ $ticket->category?->name }}</td><td class="p-4 font-mono text-xs">{{ $ticket->public_code }}</td><td class="p-4">{{ str_replace('_',' ',$ticket->status) }}</td></tr>@empty<tr><td colspan="4" class="p-4 text-cream/50">No tickets issued yet.</td></tr>@endforelse</tbody></table>
    </div>
</div>
@endsection
