<?php

namespace Database\Seeders;

use App\Models\Event;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $event = Event::firstOrCreate(
            ['name' => env('TICKET_EVENT_NAME', 'A SSeason to Bloom by Sheila Saukia')],
            [
                'description' => 'A SSeason to Bloom merupakan perayaan perjalanan dan babak baru Sheila Saukia melalui fashion show yang terinspirasi dari pertumbuhan serta keindahan bunga lotus. Acara menghadirkan koleksi busana pilihan, pengalaman berbelanja di SS Bazaar, dan rangkaian pertunjukan dalam suasana yang elegan.',
                'event_starts_at' => env('TICKET_EVENT_DATE') ? env('TICKET_EVENT_DATE').' 00:00:00' : null,
                'venue' => env('TICKET_EVENT_VENUE', 'Tennis Indoor Senayan, kawasan Gelora Bung Karno, Jakarta Pusat'),
                'registration_opens_at' => env('TICKET_WAR_OPENS_AT') ?: null,
                'registration_closes_at' => env('TICKET_WAR_CLOSES_AT') ?: null,
                'is_active' => true,
            ]
        );

        $tiers = [
            ['name' => 'Cat 1', 'color' => '#D7BD72', 'quota' => env('TICKET_CAT1_QUOTA', 100)],
            ['name' => 'Cat 2', 'color' => '#D99A9A', 'quota' => env('TICKET_CAT2_QUOTA', 100)],
            ['name' => 'Cat 3', 'color' => '#91A889', 'quota' => env('TICKET_CAT3_QUOTA', 100)],
            ['name' => 'Cat 4', 'color' => '#91AFC1', 'quota' => env('TICKET_CAT4_QUOTA', 100)],
            ['name' => 'Cat 5', 'color' => '#B19BCB', 'quota' => env('TICKET_CAT5_QUOTA', 100)],
        ];

        foreach ($tiers as $tier) {
            $event->categories()->firstOrCreate(
                ['name' => $tier['name']],
                [
                    'quota' => (int) $tier['quota'],
                    'sold' => 0,
                    'max_per_order' => 1,
                    'display_color' => $tier['color'],
                    'is_active' => true,
                ]
            );
        }

        // Disable the original starter category so only the five configured tiers are offered.
        $event->categories()->where('name', 'Free Ticket')->update(['is_active' => false]);
    }
}
