@extends('layouts.app')
@section('content')
<div class="py-10">
    <a href="{{ route('admin.dashboard') }}" class="text-sm text-gold">← Admin dashboard</a>
    <h1 class="mt-3 font-display text-5xl">Registered users</h1>
    <form class="mt-6 flex gap-2"><input name="q" value="{{ request('q') }}" placeholder="Search name, email or phone" class="form-input"><button class="rounded-xl bg-gold px-5 font-semibold text-olive">Search</button></form>
    <div class="mt-6 overflow-x-auto rounded-2xl border border-cream/10"><table class="w-full text-left text-sm">
        <thead class="bg-black/30 text-cream/60"><tr><th class="p-4">Name</th><th class="p-4">Email</th><th class="p-4">Phone</th><th class="p-4">Tickets</th><th class="p-4">Role</th><th class="p-4">Joined</th></tr></thead>
        <tbody>@forelse($users as $user)<tr class="border-t border-cream/10"><td class="p-4">{{ $user->name }}</td><td class="p-4">{{ $user->email }}</td><td class="p-4">{{ $user->phone ?: '—' }}</td><td class="p-4">{{ $user->tickets_count }}</td><td class="p-4">{{ $user->is_admin ? 'Admin' : 'Attendee' }}</td><td class="p-4">{{ $user->created_at->format('d M Y') }}</td></tr>@empty<tr><td colspan="6" class="p-4">No users found.</td></tr>@endforelse</tbody>
    </table></div>
    <div class="mt-6">{{ $users->links() }}</div>
</div>
@endsection
