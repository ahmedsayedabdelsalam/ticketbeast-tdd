<?php

use App\Concert;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ConcertSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(Concert::class)
            ->state('published')
            ->create([
                'title' => 'The Red Chord',
                'subtitle' => 'with Animosity and Lethargy',
                'venue' => 'The Mosh Pit',
                'venue_address' => '123 Example Lane',
                'city' => 'Laraville',
                'state' => 'ON',
                'zip' => '17916',
                'date' => Carbon::parse('2020-07-07 8:00pm'),
                'ticket_price' => 3250,
                'additional_information' => 'This concert is 19+.',
            ])
            ->addTickets(10);
    }
}
