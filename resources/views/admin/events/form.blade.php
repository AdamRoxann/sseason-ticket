@extends('layouts.app')
@section('content')
<div class="py-10">
    <a href="{{ route('admin.events') }}" class="text-sm text-gold">← All events</a>
    <h1 class="mt-3 font-display text-5xl">{{ $event->exists ? 'Manage event' : 'Create event' }}</h1>

    <form class="mt-8 space-y-5 rounded-2xl border border-gold/20 bg-black/20 p-6" method="POST"
        action="{{ $event->exists ? route('admin.events.update', $event) : route('admin.events.store') }}" enctype="multipart/form-data">
        @csrf
        @if($event->exists) @method('PUT') @endif
        <div>
            <label class="mb-2 block text-sm text-cream/70">Event name *</label>
            <input class="form-input w-full" name="name" value="{{ old('name', $event->name) }}" required maxlength="255">
            @error('name')<p class="mt-1 text-sm text-red-300">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-2 block text-sm text-cream/70">Description</label>
            <textarea class="form-input min-h-28 w-full" name="description">{{ old('description', $event->description) }}</textarea>
        </div>
        <div class="grid gap-4 md:grid-cols-2">
            <div><label class="mb-2 block text-sm text-cream/70">Venue</label><input class="form-input w-full" name="venue" value="{{ old('venue', $event->venue) }}"></div>
            <div><label class="mb-2 block text-sm text-cream/70">Event date & time</label><input class="form-input w-full" type="datetime-local" name="event_starts_at" value="{{ old('event_starts_at', $event->event_starts_at?->format('Y-m-d\TH:i')) }}"></div>
            <div><label class="mb-2 block text-sm text-cream/70">Registration opens</label><input class="form-input w-full" type="datetime-local" name="registration_opens_at" value="{{ old('registration_opens_at', $event->registration_opens_at?->format('Y-m-d\TH:i')) }}"></div>
            <div><label class="mb-2 block text-sm text-cream/70">Registration closes</label><input class="form-input w-full" type="datetime-local" name="registration_closes_at" value="{{ old('registration_closes_at', $event->registration_closes_at?->format('Y-m-d\TH:i')) }}"></div>
        </div>
        <div>
            <label class="mb-2 block text-sm text-cream/70">Floor plan image (JPG, PNG or WebP; max 5 MB)</label>
            <input class="block w-full text-sm text-cream/70" type="file" name="floor_plan" accept=".jpg,.jpeg,.png,.webp">
            @if($event->floor_plan_path)
                <img class="mt-4 max-h-72 rounded-xl bg-white object-contain p-2" src="{{ Storage::disk('public')->url($event->floor_plan_path) }}" alt="Current event floor plan">
            @else
                <img class="mt-4 max-h-72 rounded-xl bg-white object-contain p-2" src="{{ asset('images/floor-plan-reference.jpg') }}" alt="Reference floor plan">
                <p class="mt-1 text-xs text-cream/50">Currently using the supplied reference image. Upload an event-specific floor plan to replace it.</p>
            @endif
            @error('floor_plan')<p class="mt-1 text-sm text-red-300">{{ $message }}</p>@enderror
        </div>
        <label class="flex items-center gap-3 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $event->exists ? $event->is_active : true))> Set as active event</label>
        <button class="rounded-full bg-gold px-7 py-3 font-semibold text-olive">Save event settings</button>
    </form>

    @if($event->exists)
    <section class="mt-12">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div><p class="text-xs uppercase tracking-[.3em] text-gold">Ticket setup</p><h2 class="mt-2 font-display text-4xl">Ticket tiers</h2></div>
            <p class="text-sm text-cream/55">Names, quota and display colors can be changed here. Each account is limited to one ticket.</p>
        </div>

        <form method="POST" action="{{ route('admin.categories.store', $event) }}" class="mt-6 grid gap-3 rounded-2xl border border-gold/20 bg-black/20 p-5 md:grid-cols-12">
            @csrf
            <div class="md:col-span-4"><label class="mb-1 block text-xs text-cream/60">Tier name</label><input class="form-input w-full" name="name" placeholder="Cat 1" required></div>
            <div class="md:col-span-2"><label class="mb-1 block text-xs text-cream/60">Quota</label><input class="form-input w-full" type="number" min="1" name="quota" value="100" required></div>
            <div class="md:col-span-2"><label class="mb-1 block text-xs text-cream/60">Color</label><input class="h-11 w-full rounded-lg border border-cream/20 bg-black/30 p-1" type="color" name="display_color" value="#D7BD72"></div>
            <div class="md:col-span-2 flex items-end"><label class="flex items-center gap-2 pb-3 text-sm"><input type="checkbox" name="is_active" value="1" checked> Active</label></div>
            <input type="hidden" name="max_per_order" value="1">
            <div class="md:col-span-2"><button class="w-full rounded-full bg-gold px-4 py-3 font-semibold text-olive">Add tier</button></div>
            @error('category')<p class="text-sm text-red-300 md:col-span-12">{{ $message }}</p>@enderror
        </form>

        <div class="mt-5 space-y-4">
            @forelse($categories as $category)
                <form method="POST" action="{{ route('admin.categories.update', [$event, $category]) }}" class="grid gap-3 rounded-2xl border border-cream/10 bg-black/20 p-5 md:grid-cols-12">
                    @csrf @method('PUT')
                    <div class="md:col-span-4"><label class="mb-1 block text-xs text-cream/60">Tier name</label><input class="form-input w-full" name="name" value="{{ $category->name }}" required></div>
                    <div class="md:col-span-2"><label class="mb-1 block text-xs text-cream/60">Total quota</label><input class="form-input w-full" type="number" min="{{ max(1, $category->sold) }}" name="quota" value="{{ $category->quota }}" required><p class="mt-1 text-xs text-cream/45">{{ $category->sold }} sold</p></div>
                    <div class="md:col-span-2"><label class="mb-1 block text-xs text-cream/60">Color</label><input class="h-11 w-full rounded-lg border border-cream/20 bg-black/30 p-1" type="color" name="display_color" value="{{ $category->display_color ?? '#D7BD72' }}"></div>
                    <div class="md:col-span-2 flex items-end"><label class="flex items-center gap-2 pb-3 text-sm"><input type="checkbox" name="is_active" value="1" @checked($category->is_active)> Active</label></div>
                    <input type="hidden" name="max_per_order" value="1">
                    <div class="flex items-end gap-2 md:col-span-2"><button class="flex-1 rounded-full border border-gold/50 px-3 py-3 text-sm text-gold">Save</button></div>
                </form>
                <form method="POST" action="{{ route('admin.categories.destroy', [$event, $category]) }}" class="-mt-3 text-right" onsubmit="return confirm('Delete this tier? Tiers with issued tickets cannot be deleted.')">
                    @csrf @method('DELETE')<button class="text-xs text-red-300 underline">Delete {{ $category->name }}</button>
                </form>
            @empty
                <p class="rounded-xl border border-cream/10 p-5 text-sm text-cream/55">No tiers yet. Add the ticket categories above.</p>
            @endforelse
        </div>
    </section>
    @endif
</div>
@endsection
