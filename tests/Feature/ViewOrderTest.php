<?php


namespace Tests\Feature;


use App\Concert;
use App\Order;
use App\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ViewOrderTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function user_can_view_their_order_confirmation()
    {
        $this->withoutExceptionHandling();
        $concert = factory(Concert::class)->state('published')->create();
        $order = factory(Order::class)->create([
            'confirmation_number' => 'ORDERCONFIRMATION1234'
        ]);
        $ticket = factory(Ticket::class)->create([
            'concert_id' => $concert->id,
            'order_id' => $order->id
        ]);

        $response = $this->get("/orders/ORDERCONFIRMATION1234");

        $response->assertStatus(200);
        $response->assertViewHas('order', $order);
    }
}
