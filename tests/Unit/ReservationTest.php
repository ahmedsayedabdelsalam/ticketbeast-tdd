<?php

namespace Tests\Unit;

use App\Billing\FakePaymentGateway;
use App\Concert;
use App\Reservation;
use App\Ticket;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class ReservationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function calculating_the_total_cost()
    {
        $tickets = collect([
            (object) ['price' => 1200],
            (object) ['price' => 1200],
            (object) ['price' => 1200],
        ]);

        $reservation = new Reservation($tickets, 'ahmed@example.com');

        $this->assertEquals(3600, $reservation->totalCost());
    }

    /** @test */
    function retrieving_the_reservation_tickets()
    {
        $tickets = collect([
            (object) ['price' => 1200],
            (object) ['price' => 1200],
            (object) ['price' => 1200],
        ]);

        $reservation = new Reservation($tickets, 'ahmed@example.com');

        $this->assertEquals($tickets, $reservation->tickets());
    }

    /** @test */
    function retrieving_the_reservation_email()
    {
        $tickets = collect();

        $reservation = new Reservation($tickets, $email = 'ahmed@example.com');

        $this->assertEquals($email, $reservation->email());
    }

    /** @test */
    function reserved_tickets_are_released_when_a_reservation_is_cancelled()
    {
        $tickets = new Collection([
            Mockery::spy(Ticket::class),
            Mockery::spy(Ticket::class),
            Mockery::spy(Ticket::class),
        ]);

        $reservation = new Reservation($tickets, 'ahmed@example.com');

        $reservation->cancel();

        foreach ($tickets as $ticket) {
            $ticket->shouldHaveReceived('release');
        }
    }

    /** @test */
    function completing_a_reservation()
    {
        $concert = factory(Concert::class)
            ->state('published')
            ->create(['ticket_price' => 1200])
            ->addTickets(3);
        $tickets = $concert->findTickets(3);
        $paymentGateway = new FakePaymentGateway();

        $reservation = new Reservation($tickets, $email = 'ahmed@example.com');
        $order = $reservation->complete($paymentGateway, $paymentGateway->getValidTestToken());

        $this->assertEquals($email, $order->email);
        $this->assertEquals(3, $order->ticketQuantity());
        $this->assertEquals(3600, $order->amount);
        $this->assertEquals(3600, $paymentGateway->totalCharges());
    }
}
