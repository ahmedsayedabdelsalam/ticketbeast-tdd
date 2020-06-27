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
    public function tickets_are_released_when_an_order_is_cancelled()
    {
        $concert = factory(Concert::class)->state('published')->create()->addTickets(10);
        $order = $concert->orderTickets('ahmed@example.com', 5);
        $this->assertEquals(5, $concert->ticketsRemaining());

        $order->cancel();

        $this->assertEquals(10, $concert->ticketsRemaining());
        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
    }

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
