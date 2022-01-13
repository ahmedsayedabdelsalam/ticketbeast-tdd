<?php

namespace Tests\Unit;

use App\Concert;
use App\Order;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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

    /** @test */
    function retrieving_an_order_by_confirmation_number()
    {
        $order = factory(Order::class)->create([
            'confirmation_number' => 'ORDERCONFIRMATION1234'
        ]);

        $foundOrder = Order::findByConfirmationNumber('ORDERCONFIRMATION1234');

        $this->assertEquals($order->id, $foundOrder->id);
    }

    /** @test */
    function retrieving_a_nonexistent_order_by_confirmation_number_throws_an_exception()
    {
        $this->expectException(ModelNotFoundException::class);

        Order::findByConfirmationNumber('ORDERCONFIRMATION1234');
    }
}
