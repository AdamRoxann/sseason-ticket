@extends('layouts.app')
@section('content')
<div class="py-10">
    <a href="{{ route('admin.dashboard') }}" class="text-sm text-gold">← Admin dashboard</a>
    <h1 class="mt-3 font-display text-5xl">Tickets & QR codes</h1>
    <form class="mt-6 flex gap-2"><input name="q" value="{{ request('q') }}" placeholder="Search ticket code, attendee or email" class="form-input"><button class="rounded-xl bg-gold px-5 font-semibold text-olive">Search</button></form>
    <div class="mt-6 overflow-x-auto rounded-2xl border border-cream/10"><table class="w-full text-left text-sm">
        <thead class="bg-black/30 text-cream/60"><tr><th class="p-4">Attendee</th><th class="p-4">Email</th><th class="p-4">Tier</th><th class="p-4">Ticket / QR</th><th class="p-4">Status</th><th class="p-4">Issued</th></tr></thead>
        <tbody>@forelse($tickets as $ticket)<tr class="border-t border-cream/10 align-middle"><td class="p-4">{{ $ticket->user?->name }}</td><td class="p-4">{{ $ticket->user?->email }}</td><td class="p-4">{{ $ticket->category?->name }}</td><td class="p-4"><div class="font-mono text-xs">{{ $ticket->public_code }}</div>@if($ticket->qr_path)<a class="mt-2 inline-block text-gold underline" target="_blank" href="{{ Storage::disk('public')->url($ticket->qr_path) }}">Open QR SVG</a>@endif</td><td class="p-4">{{ str_replace('_',' ',$ticket->status) }}</td><td class="p-4">{{ $ticket->created_at->format('d M Y H:i') }}</td></tr>@empty<tr><td colspan="6" class="p-4">No tickets found.</td></tr>@endforelse</tbody>
    </table></div>
    <div class="mt-6">{{ $tickets->links() }}</div>
</div>
@endsection
