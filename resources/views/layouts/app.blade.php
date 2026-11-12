<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'A SSeason to Bloom' }} · Sheila Saukia</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="min-h-screen bg-olive text-cream antialiased">
<header class="mx-auto flex max-w-7xl items-center justify-between px-6 py-6">
    <a href="{{ route('home') }}" class="font-display text-3xl tracking-tight">SS<span class="text-gold">.</span></a>
    <nav class="flex items-center gap-4 text-sm">
        @auth <a href="{{ route('tickets.index') }}" class="hover:text-gold">My Tickets</a>
        {{-- <a href="{{ route('scan.index') }}" class="hover:text-gold">Scan / Check-in</a> --}}
        <form method="POST" action="{{ route('logout') }}">@csrf<button class="hover:text-gold">Logout</button></form>
        @else <a href="{{ route('login') }}" class="hover:text-gold">Login</a><a href="{{ route('register') }}" class="rounded-full bg-gold px-4 py-2 font-semibold text-olive">Create Account</a> @endauth
    </nav>
</header>
<main class="mx-auto max-w-7xl px-6 pb-20">@if(session('success'))<div class="mb-6 rounded-xl border border-gold/40 bg-gold/10 p-4 text-gold">{{ session('success') }}</div>@endif @yield('content')</main>
<footer class="border-t border-cream/10 px-6 py-8 text-center text-xs text-cream/50">© {{ date('Y') }} ASIARIGHT. All Rights Reserved.</footer>
</body></html>
