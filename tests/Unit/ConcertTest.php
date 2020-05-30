<?php

namespace Tests\Unit;

use App\Concert;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConcertTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function can_get_formatted_date()
    {
        $concert = factory(Concert::class)->make([
            'date' => Carbon::parse('2016-12-01 8:00pm')
        ]);

        $this->assertEquals('December 1, 2016', $concert->formatted_date);
    }

    /** @test */
    function can_get_formatted_start_time()
    {
        $concert = factory(Concert::class)->make([
            'date' => Carbon::parse('2016-12-01 17:00:00')
        ]);

        $this->assertEquals('5:00 pm', $concert->formatted_start_time);
    }

    /** @test */
    function can_get_price_in_dollars()
    {
        $concert = factory(Concert::class)->make([
            'ticket_price' => 2050
        ]);

        $this->assertEquals('20.50', $concert->price_in_dollars);
    }

    /** @test */
    function can_order_concert_tickets()
    {
        $concert = factory(Concert::class)->state('published')->create();

        $order = $concert->orderTickets($email = 'ahmed@example.com', $ticket_quantity = 3);

        $this->assertEquals($email, $order->email);
        $this->assertEquals($ticket_quantity, $order->tickets()->count());
    }
}
