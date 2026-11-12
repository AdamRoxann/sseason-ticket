<?php
namespace Database\Seeders;
use App\Models\Event;
use Illuminate\Database\Seeder;
class DatabaseSeeder extends Seeder {
    public function run(): void {
        $event = Event::firstOrCreate(['name'=>env('TICKET_EVENT_NAME','A SSeason to Bloom by Sheila Saukia')], [
            'description'=>'A SSeason to Bloom merupakan perayaan perjalanan dan babak baru Sheila Saukia melalui fashion show yang terinspirasi dari pertumbuhan serta keindahan bunga lotus. Acara menghadirkan koleksi busana pilihan, pengalaman berbelanja di SS Bazaar, dan rangkaian pertunjukan dalam suasana yang elegan.',
            'event_starts_at'=>env('TICKET_EVENT_DATE') ? env('TICKET_EVENT_DATE').' 00:00:00' : null,
            'venue'=>env('TICKET_EVENT_VENUE','Tennis Indoor Senayan, kawasan Gelora Bung Karno, Jakarta Pusat'),
            'registration_opens_at'=>env('TICKET_WAR_OPENS_AT') ?: null,
            'registration_closes_at'=>env('TICKET_WAR_CLOSES_AT') ?: null, 'is_active'=>true,
        ]);
        $event->categories()->firstOrCreate(['name'=>'Free Ticket'], ['quota'=>(int)env('TICKET_DEFAULT_QUOTA',0),'sold'=>0,'max_per_order'=>(int)env('TICKET_MAX_PER_USER',1),'is_active'=>true]);
    }
}
