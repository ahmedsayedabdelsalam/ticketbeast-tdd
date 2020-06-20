<?php

namespace Tests\Unit;

use App\Concert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function tickets_can_be_released()
    {
        $concert = factory(Concert::class)->state('published')->create();
        $concert->addTickets(1);
        $order = $concert->orderTickets('ahmed@example.com', 1);
        $this->assertDatabaseHas('tickets', ['order_id' => $order->id, 'concert_id' => $concert->id]);

        $order->tickets()->release();

        $this->assertDatabaseMissing('tickets', ['order_id' => $order->id, 'concert_id' => $concert->id]);
    }

    /** @test */
    public function a_single_ticket_can_be_released_throw_model()
    {
        $concert = factory(Concert::class)->state('published')->create()->addTickets(1);
        $order = $concert->orderTickets('ahmed@example.com', 1);
        $ticket = $order->tickets()->first();
        $this->assertDatabaseHas('tickets', ['order_id' => $order->id, 'concert_id' => $concert->id]);

        $ticket->release();

        $this->assertDatabaseMissing('tickets', ['order_id' => $order->id, 'concert_id' => $concert->id]);
    }
}
