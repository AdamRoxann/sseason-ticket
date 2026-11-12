# A SSeason to Bloom — Free Ticket War Starter

Laravel **12.70.1** starter for the Sheila Saukia free-ticket microsite. This is a development foundation, not a production-ready high-volume ticketing platform. It includes registration/login, event seed data, Redis-backed waiting-room ordering, a basic admission window, ticket quota locking, ticket records, and SVG QR generation.

## Stack

- PHP 8.2+
- Laravel Framework 12.70.1 (pinned in `composer.json`)
- MySQL 8
- Redis (queue/cache/waiting-room sorted set; Predis client)
- Laravel Reverb + Echo (WebSocket layer; finish setup below)
- Blade + Tailwind CSS 4 + Vite
- Endroid QR Code (SVG ticket QR)

## Event data included

- Event: **A SSeason to Bloom by Sheila Saukia**
- Description: A SSeason to Bloom merupakan perayaan perjalanan dan babak baru Sheila Saukia melalui fashion show yang terinspirasi dari pertumbuhan serta keindahan bunga lotus. Acara menghadirkan koleksi busana pilihan, pengalaman berbelanja di SS Bazaar, dan rangkaian pertunjukan dalam suasana yang elegan.
- Venue: **Tennis Indoor Senayan, kawasan Gelora Bung Karno, Jakarta Pusat**
- Fashion-show date: left unset because the supplied 6 December 2026 date refers to a bazaar promotion, not a confirmed show date.
- Ticket war opening, close time, quota, active checkout sessions, and ticket limit are configurable through `.env`.

## Setup

1. Install PHP 8.2+, Composer, Node.js 20+, MySQL 8, and Redis.
2. Create a MySQL database named `free_ticket_war`.
3. Copy `.env.example` to `.env`, then configure DB and Redis credentials and confirm the event/ticket-war schedule and quota.
4. Install dependencies and generate the app key:

   ```bash
   composer install
   cp .env.example .env
   php artisan key:generate
   npm install
   ```

5. Run migrations and seed the event/category:

   ```bash
   php artisan migrate --seed
   php artisan storage:link
   ```

6. Install Reverb broadcasting scaffolding (this command adds the Laravel Echo client configuration and broadcasting config for your installed Laravel version):

   ```bash
   php artisan install:broadcasting --reverb
   ```

   The installer configures the Reverb connection and Echo client. Review the generated `.env` Reverb credentials; then build frontend assets:

   ```bash
   npm run build
   ```

7. Run the local services in separate terminals:

   ```bash
   php artisan serve
   php artisan queue:work --tries=3
   php artisan reverb:start
   npm run dev
   ```

## Important implementation notes

- The waiting-room queue is stored in a Redis sorted set with a database record per user/event. The first-in ordering uses server-side timestamps.
- The starter admits up to `TICKET_ACTIVE_SESSIONS` active checkout sessions. An admitted session expires after `TICKET_ACCESS_MINUTES`.
- The queue page currently uses HTTP polling as a working fallback. Reverb is included in the stack and a private per-user broadcast event/channel is scaffolded, but the Echo listener and full production broadcast wiring still need to be completed after running the broadcasting installer.
- Ticket allocation uses a database transaction and `lockForUpdate()` on the category row. This protects the quota from concurrent checkouts, assuming a transactional database such as MySQL/InnoDB.
- QR codes are generated as SVG files and stored on the public disk. The QR payload currently points to the authenticated ticket detail URL; for actual venue scanning, replace this with a dedicated signed/public validation URL and build a staff check-in screen that validates and atomically marks each ticket as checked in.
- The current queue admission is a simple starter implementation. For a real high-demand ticket war, use a dedicated queue processor/worker, atomic Redis Lua operations or a carefully designed admission lock, queue heartbeat/abandonment handling, rate limits, bot protection, idempotent checkout, and load testing. Do not launch a high-traffic sale using this starter unchanged.
- Do not use browser countdowns or client-provided queue positions as authority. All admission and quota decisions must be server-side.

## Configure ticket-war values

Edit `.env`:

```dotenv
TICKET_WAR_OPENS_AT="YYYY-MM-DD HH:MM:SS"
TICKET_WAR_CLOSES_AT="YYYY-MM-DD HH:MM:SS"
TICKET_DEFAULT_QUOTA=500
TICKET_MAX_PER_USER=2
TICKET_ACTIVE_SESSIONS=100
TICKET_ACCESS_MINUTES=5
```

The example values above must be replaced with approved operational settings. The included `.env.example` leaves the opening/closing schedule blank and sets quota to zero, so ticket war cannot start until the event is configured. Do not use the illustrative values as confirmed event information. After changing config values in production, run `php artisan config:cache`.

## Before production

- Complete Reverb/Echo private-channel listener and queue-state broadcasts.
- Build an admin dashboard with event/category quota controls and registration exports.
- Build a public/signed QR validation endpoint and staff check-in interface.
- Add email/phone verification, rate limiting, CAPTCHA/Turnstile, audit logs, and abuse controls.
- Add idempotency keys and robust queue expiry/re-entry logic.
- Configure HTTPS, secure WebSocket proxy, Supervisor/systemd workers, Redis persistence/monitoring, backups, and logs.
- Run concurrent load tests against realistic queue bursts and test oversell prevention.

## Official docs

- Laravel 12 Reverb: https://laravel.com/docs/12.x/reverb
- Laravel 12 Broadcasting: https://laravel.com/docs/12.x/broadcasting
