@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-3xl py-8 sm:py-12">
    <div class="mb-8 text-center">
        <p class="text-xs font-semibold uppercase tracking-[.28em] text-gold">Event Operations</p>
        <h1 class="mt-3 font-display text-5xl text-cream sm:text-6xl">QR Check-in</h1>
        <p class="mx-auto mt-3 max-w-xl text-sm leading-6 text-cream/65">
            Scan the attendee's ticket QR code to verify it and mark the ticket as used.
        </p>
    </div>

    <div class="overflow-hidden rounded-3xl border border-gold/20 bg-olive/80 shadow-2xl shadow-black/20 backdrop-blur">
        <div class="border-b border-cream/10 p-5 sm:p-7">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold text-cream">Camera scanner</h2>
                    <p class="mt-1 text-xs text-cream/50">Allow camera access when your browser asks.</p>
                </div>
                <span id="camera-status" class="rounded-full border border-cream/15 px-3 py-1 text-xs text-cream/60">Camera stopped</span>
            </div>

            <div class="relative mt-5 overflow-hidden rounded-2xl bg-black" style="aspect-ratio: 4 / 3;">
                <video id="qr-video" class="h-full w-full object-cover" autoplay muted playsinline></video>
                <div id="camera-placeholder" class="absolute inset-0 flex flex-col items-center justify-center gap-3 px-6 text-center text-cream/55">
                    <svg class="h-12 w-12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" aria-hidden="true"><path d="M3 7h3l2-3h8l2 3h3v13H3z"/><circle cx="12" cy="13" r="4"/></svg>
                    <span class="text-sm">Camera preview will appear here</span>
                </div>
                <div id="scan-frame" class="pointer-events-none absolute inset-[17%] hidden rounded-2xl border-2 border-gold shadow-[0_0_0_999px_rgba(0,0,0,.18)]"></div>
            </div>

            <div class="mt-4 flex flex-wrap gap-3">
                <button id="start-camera" type="button" class="rounded-full bg-gold px-5 py-3 text-sm font-bold text-olive transition hover:bg-[#ead59b]">Start camera</button>
                <button id="stop-camera" type="button" class="rounded-full border border-cream/20 px-5 py-3 text-sm font-semibold text-cream transition hover:border-gold/60" disabled>Stop camera</button>
            </div>
            <p id="camera-help" class="mt-3 text-xs leading-5 text-cream/45">
                Camera scanning requires a supported browser and HTTPS (localhost is also allowed).
            </p>
        </div>

        <div class="p-5 sm:p-7">
            <h2 class="text-lg font-semibold text-cream">Or enter QR content manually</h2>
            <form id="manual-scan-form" action="{{ route('admin.scan.check-in') }}" method="POST" class="mt-4 space-y-3">
                @csrf
                <label for="qr-value" class="block text-xs font-medium uppercase tracking-wider text-cream/55">Ticket QR URL or ticket code</label>
                <input id="qr-value" name="qr_value" type="text" autocomplete="off" required maxlength="2048"
                    placeholder="Paste the ticket QR content here"
                    class="w-full rounded-xl border border-cream/15 bg-black/20 px-4 py-3 text-sm text-cream outline-none transition placeholder:text-cream/30 focus:border-gold">
                <button id="manual-submit" type="submit" class="w-full rounded-xl border border-gold/50 px-5 py-3 text-sm font-bold text-gold transition hover:bg-gold/10">Verify & check in</button>
            </form>

            <div id="scan-result" class="mt-5 hidden rounded-2xl border p-5" role="status" aria-live="polite">
                <p id="result-heading" class="font-semibold"></p>
                <p id="result-message" class="mt-1 text-sm"></p>
                <dl id="result-details" class="mt-4 hidden grid-cols-1 gap-3 border-t border-current/10 pt-4 text-sm sm:grid-cols-2"></dl>
            </div>
        </div>
    </div>
    <p class="mt-5 text-center text-xs text-cream/40">Only authenticated accounts can access this page. Check-in actions are recorded against the ticket.</p>
</div>
@endsection
