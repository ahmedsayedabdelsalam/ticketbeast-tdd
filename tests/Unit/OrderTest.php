<?php

namespace Tests\Unit;

use App\Concert;
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
}
