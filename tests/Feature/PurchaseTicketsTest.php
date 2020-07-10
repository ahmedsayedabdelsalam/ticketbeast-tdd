<?php

namespace Tests\Feature;

use App\Billing\FakePaymentGateway;
use App\Billing\PaymentGateway;
use App\Concert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PurchaseTicketsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var FakePaymentGateway
     */
    private $paymentGateway;

    protected function setUp(): void
    {
        parent::setUp();

        $this->paymentGateway = new FakePaymentGateway;
        $this->app->instance(PaymentGateway::class, $this->paymentGateway);
    }

    /** @test */
    function customer_can_purchase_tickets_to_a_published_concert()
    {
        // Arrange
        // Create a concert
        $concert = factory(Concert::class)->state('published')->create([
            'ticket_price' => 3250
        ])->addTickets($ticket_quantity = 3);

        // Act
        // Purchase concert tickets
        $response = $this->json('POST', route('concerts.orders', $concert->id), [
            'email' => $email = 'ahmed@example.com',
            'ticket_quantity' => $ticket_quantity,
            'payment_token' => $this->paymentGateway->getValidTestToken()
        ]);

        // Assert
        // Make sure request succeeded
        $response->assertStatus(201);
        $response->assertJsonFragment([
            'email' => $email,
            'ticket_quantity' => $ticket_quantity
        ]);
        // Make sure that the customer was charged the correct amount
        $this->assertEquals(9750, $this->paymentGateway->totalCharges());
        // Make sure that an order exists for this customer
        $this->assertTrue($concert->hasOrderFor('ahmed@example.com'));
        $this->assertEquals($ticket_quantity, $concert->orderFor('ahmed@example.com')->first()->ticketQuantity());
    }

    /** @test */
    function cannot_purchase_tickets_to_an_unpublished_concert()
    {
        $concert = factory(Concert::class)->state('unpublished')->create()->addTickets($ticket_quantity = 3);

        $response = $this->json('POST', route('concerts.orders', $concert->id), [
            'email' => 'ahmed@example.com',
            'ticket_quantity' => $ticket_quantity,
            'payment_token' => $this->paymentGateway->getValidTestToken()
        ]);

        $response->assertStatus(404);
        $this->assertEquals(0, $this->paymentGateway->totalCharges());
        $this->assertDatabaseMissing('orders', ['concert_id' => $concert->id]);
    }

    /** @test */
    function an_order_is_not_created_if_payment_fails()
    {
        $this->withoutExceptionHandling();
        $concert = factory(Concert::class)->state('published')->create([
            'ticket_price' => 3250
        ])->addTickets($ticket_quantity = 3);

        $response = $this->json('POST', route('concerts.orders', $concert->id), [
            'email' => 'ahmed@example.com',
            'ticket_quantity' => $ticket_quantity,
            'payment_token' => 'invalid-payment-token'
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseMissing('orders', ['concert_id' => $concert->id]);
        $this->assertEquals(3, $concert->ticketsRemaining());
    }

    /** @test */
    function email_is_required_to_purchase_tickets()
    {
        $concert = factory(Concert::class)->state('published')->create()->addTickets($ticket_quantity = 3);

        $response = $this->json('POST', route('concerts.orders', $concert->id), [
            'ticket_quantity' => $ticket_quantity,
            'payment_token' => $this->paymentGateway->getValidTestToken()
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('email');
    }

    /** @test */
    public function cannot_purchase_more_tickets_that_remain()
    {
        $concert = factory(Concert::class)->state('published')->create()->addTickets(50);

        $response = $this->json('POST', route('concerts.orders', $concert->id), [
            'email' => 'ahmed@example.com',
            'ticket_quantity' => 51,
            'payment_token' => $this->paymentGateway->getValidTestToken()
        ]);

        $response->assertStatus(422);
        $this->assertFalse($concert->hasOrderFor('ahmed@example.com'));
        $this->assertEquals(0, $this->paymentGateway->totalCharges());
        $this->assertEquals(50, $concert->ticketsRemaining());
    }

    /** @test */
    function cannot_purchase_tickets_another_customer_is_already_trying_to_purchase()
    {
        $this->withoutExceptionHandling();
        $concert = factory(Concert::class)->state('published')->create([
            'ticket_price' => 1200
        ])->addTickets(3);

        $this->paymentGateway->beforeFirstCharge(function ($paymentGateway) use ($concert) {
            $requestA = $this->app['request'];
            $response = $this->json('POST', route('concerts.orders', $concert->id), [
                'email' => 'personB@example.com',
                'ticket_quantity' => 1,
                'payment_token' => $this->paymentGateway->getValidTestToken()
            ]);
            $this->app['request'] = $requestA;

            $response->assertStatus(422);
            $this->assertFalse($concert->hasOrderFor('personB@example.com'));
            $this->assertEquals(0, $this->paymentGateway->totalCharges());
        });

        $response = $this->json('POST', route('concerts.orders', $concert->id), [
            'email' => 'personA@example.com',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken()
        ]);

        $response->assertStatus(201);
        $response->assertJsonFragment([
            'email' => 'personA@example.com',
            'ticket_quantity' => 3
        ]);
        $this->assertEquals(3600, $this->paymentGateway->totalCharges());
        $this->assertTrue($concert->hasOrderFor('personA@example.com'));
        $this->assertEquals(3, $concert->orderFor('personA@example.com')->first()->ticketQuantity());
    }

    /** @test */
    function email_must_be_valid_to_purchase_tickets()
    {
        $concert = factory(Concert::class)->state('published')->create()->addTickets($ticket_quantity = 3);

        $response = $this->json('POST', route('concerts.orders', $concert->id), [
            'email' => 'not-valid-email-address',
            'ticket_quantity' => $ticket_quantity,
            'payment_token' => $this->paymentGateway->getValidTestToken()
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('email');
    }

    /** @test */
    function ticket_quantity_must_be_at_least_1_to_purchase_tickets()
    {
        $concert = factory(Concert::class)->state('published')->create()->addTickets(1);

        $response = $this->json('POST', route('concerts.orders', $concert->id), [
            'email' => 'ahmed@example.com',
            'ticket_quantity' => 0,
            'payment_token' => $this->paymentGateway->getValidTestToken()
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('ticket_quantity');
    }

    /** @test */
    function payment_token_is_required_to_purchase_tickets()
    {
        $concert = factory(Concert::class)->state('published')->create()->addTickets($ticket_quantity = 3);

        $response = $this->json('POST', route('concerts.orders', $concert->id), [
            'email' => 'ahmed@example.com',
            'ticket_quantity' => $ticket_quantity,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('payment_token');
    }
}
