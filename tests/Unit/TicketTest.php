<?php

namespace Tests\Unit;

use App\Concert;
use App\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function a_ticket_can_be_reserved()
    {
        $ticket = factory(Ticket::class)->create();
        $this->assertNull($ticket->reserved_at);

        $ticket->reserve();

        $this->assertNotNull($ticket->fresh()->reserved_at);
    }

    /** @test */
    function tickets_can_be_reserved()
    {
        $tickets = factory(Ticket::class, 3)->create();

        $tickets->toQuery()->reserve();

        $this->assertDatabaseMissing('tickets', [
            'reserved_at' => null
        ]);
    }

    /** @test */
    public function tickets_can_be_released()
    {
        $tickets = factory(Ticket::class, 3)->state('reserved')->create();
        $this->assertDatabaseHas('tickets', [
            'reserved_at' => $date = $tickets->first()->reserved_at,
        ]);

        $tickets->toQuery()->release();

        $this->assertDatabaseMissing('tickets', ['reserved_at' => $date]);
        $this->assertEquals(3, Ticket::available()->count());
    }

    /** @test */
    public function a_single_ticket_can_be_released_throw_model()
    {
        $ticket = factory(Ticket::class)->state('reserved')->create();
        $this->assertNotNull($ticket->reserved_at);

        $ticket->release();

        $this->assertNull($ticket->fresh()->reserved_at);
    }
}
