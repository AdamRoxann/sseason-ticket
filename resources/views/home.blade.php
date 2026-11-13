@extends('layouts.app')

@section('content')
<section class="landing-carousel" data-carousel aria-label="A SSeason to Bloom campaign banners">
    <div class="landing-carousel__viewport">
        <div class="landing-carousel__track" data-carousel-track>
            <div class="landing-carousel__slide" data-carousel-slide aria-hidden="false">
                <img
                    src="{{ asset('images/banner1.jpg') }}"
                    alt="A SSeason to Bloom by Sheila Saukia — fashion show campaign"
                    fetchpriority="high"
                >
            </div>
            <div class="landing-carousel__slide" data-carousel-slide aria-hidden="true">
                <img
                    src="{{ asset('images/banner2.png') }}"
                    alt="A SSeason to Bloom — lotus floral campaign"
                    loading="lazy"
                >
            </div>
        </div>
    </div>

    <button class="landing-carousel__arrow landing-carousel__arrow--prev" type="button"
        aria-label="Previous banner" data-carousel-prev>
        <span aria-hidden="true">‹</span>
    </button>
    <button class="landing-carousel__arrow landing-carousel__arrow--next" type="button"
        aria-label="Next banner" data-carousel-next>
        <span aria-hidden="true">›</span>
    </button>

    <div class="landing-carousel__controls" aria-label="Choose campaign banner">
        <button class="landing-carousel__dot is-active" type="button" aria-label="Show banner 1"
            aria-current="true" data-carousel-dot="0"></button>
        <button class="landing-carousel__dot" type="button" aria-label="Show banner 2"
            aria-current="false" data-carousel-dot="1"></button>
    </div>
</section>

<section class="landing-intro">
    <p class="landing-eyebrow">Sheila Saukia · Fashion Show · Jakarta</p>
    <h1 class="landing-title">Every journey has its season.<br><span>Every season has its bloom.</span></h1>
    <p class="landing-description">
        {{ $event?->description ?: 'Discover the event and reserve your ticket for an unforgettable experience.' }}
    </p>

    <div class="landing-actions">
        @auth
            <a href="{{ route('ticket-war') }}" class="landing-button landing-button--primary">Enter Ticket War</a>
            <a href="{{ route('tickets.index') }}" class="landing-button landing-button--secondary">My Tickets</a>
        @else
            <a href="{{ route('register') }}" class="landing-button landing-button--primary">Register for Tickets</a>
            <a href="{{ route('login') }}" class="landing-button landing-button--secondary">Login</a>
        @endauth
    </div>

    @if($event?->venue)<p class="landing-venue">{{ $event->venue }}</p>@endif
    <p class="landing-schedule">Event schedule: {{ $event?->event_starts_at?->timezone('Asia/Jakarta')->format('d F Y, H:i') . ' WIB' ?? 'To Be Announced (WIB)' }}</p>
</section>
@endsection
