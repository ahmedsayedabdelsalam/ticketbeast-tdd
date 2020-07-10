<?php

namespace Tests\Unit;

use App\Concert;
use App\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function creating_an_order_from_tickets_email_and_amount()
    {
        $concert = factory(Concert::class)->state('published')->create(['ticket_price' => '500'])->addTickets(10);
        $this->assertEquals(10, $concert->ticketsRemaining());

        $order = Order::forTickets($concert->findTickets(2), $email = 'ahmed@example.com', 1000);

        $this->assertEquals($email, $order->email);
        $this->assertEquals(8, $concert->ticketsRemaining());
        $this->assertEquals(2, $order->ticketQuantity());
        $this->assertEquals(1000, $order->amount);
    }
}
