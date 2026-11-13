@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-lg py-12">
    <p class="text-xs uppercase tracking-[.3em] text-gold">Join the experience</p>
    <h1 class="mt-3 font-display text-5xl">Create Account</h1>

    @if ($errors->any())
        <div role="alert" aria-live="polite" class="mt-6 rounded-lg border border-red-400/50 bg-red-950/30 p-4 text-sm text-red-200">
            <p class="font-semibold">We couldn't create your account. Please check the following:</p>
            <ul class="mt-2 list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}" class="mt-8 space-y-4">
        @csrf

        @foreach (['name' => 'Full name', 'email' => 'Email address', 'phone' => 'Phone number'] as $field => $label)
            <div>
                <label for="{{ $field }}" class="block text-sm">{{ $label }}</label>
                <input
                    id="{{ $field }}"
                    name="{{ $field }}"
                    value="{{ old($field) }}"
                    type="{{ $field === 'email' ? 'email' : 'text' }}"
                    class="form-input mt-2 w-full @error($field) border-red-400 @enderror"
                    @if ($field !== 'phone') required @endif
                    @if ($field === 'email') autocomplete="email" @endif
                >
                @error($field)
                    <p class="mt-1 text-sm text-red-300">{{ $message }}</p>
                @enderror
            </div>
        @endforeach

        <div>
            <label for="password" class="block text-sm">Password</label>
            <input
                id="password"
                name="password"
                type="password"
                required
                autocomplete="new-password"
                aria-describedby="password-error password-hint"
                class="form-input mt-2 w-full @error('password') border-red-400 @enderror"
            >
            <p id="password-hint" class="mt-2 text-xs text-cream/60">
                Use a password that meets the security requirements shown if validation fails.
            </p>
            @error('password')
                <p id="password-error" role="alert" class="mt-1 text-sm text-red-300">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm">Confirm password</label>
            <input
                id="password_confirmation"
                name="password_confirmation"
                type="password"
                required
                autocomplete="new-password"
                class="form-input mt-2 w-full @error('password') border-red-400 @enderror"
            >
            @error('password')
                <p class="mt-1 text-sm text-red-300">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="w-full rounded-full bg-gold px-6 py-3 font-semibold text-olive">
            Create account
        </button>
    </form>

    <p class="mt-6 text-sm text-cream/60">
        Already registered? <a class="text-gold" href="{{ route('login') }}">Login</a>
    </p>
</div>
@endsection
