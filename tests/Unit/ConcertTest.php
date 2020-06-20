<?php

namespace Tests\Unit;

use App\Concert;
use App\Exceptions\NotEnoughTicketsException;
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
        $concert = factory(Concert::class)->state('published')->create()->addTickets($ticket_quantity = 3);

        $order = $concert->orderTickets($email = 'ahmed@example.com', $ticket_quantity);

        $this->assertEquals($email, $order->email);
        $this->assertEquals($ticket_quantity, $order->ticketQuantity());
    }

    /** @test */
    public function can_add_tickets()
    {
        $concert = factory(Concert::class)->state('published')->create();

        $concert->addTickets(50);

        $this->assertEquals(50, $concert->ticketsRemaining());
    }

    /** @test */
    public function tickets_remaining_does_not_include_tickets_associated_with_an_order()
    {
        $concert = factory(Concert::class)->state('published')->create()->addTickets(50);
        $concert->orderTickets('ahmed@example.com', 30);

        $this->assertEquals(20, $concert->ticketsRemaining());
    }

    /** @test */
    public function trying_to_purchase_more_tickets_than_remain_throws_an_exception()
    {
        $concert = factory(Concert::class)->state('published')->create()->addTickets($quantity = 10);

        try {
            $concert->orderTickets($email = 'ahmed@example.com', $quantity + 1);
        } catch (NotEnoughTicketsException $e) {
            $this->assertFalse($concert->hasOrderFor($email));
            $this->assertEquals($quantity, $concert->ticketsRemaining());
            return;
        }

        $this->fail('Order succeeded even though there were not enough tickets remaining');
    }

    /** @test */
    public function cannot_order_tickets_that_have_already_been_purchased()
    {
        $concert = factory(Concert::class)->state('published')->create()->addTickets(10);
        $concert->orderTickets('ahmed@example.com', 8);

        try {
            $concert->orderTickets($sayedsEmail = 'sayed@example.com', 3);
        } catch (NotEnoughTicketsException $e) {
            $this->assertFalse($concert->hasOrderFor($sayedsEmail));
            $this->assertEquals(2, $concert->ticketsRemaining());
            return;
        }

        $this->fail('Order succeeded even though there were not enough tickets remaining');
    }
}
