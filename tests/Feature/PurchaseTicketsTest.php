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
        ]);

        // Act
        // Purchase concert tickets
        $response = $this->json('POST', route('concerts.orders', $concert->id), [
            'email' => 'ahmed@example.com',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken()
        ]);

        // Assert
        // Make sure request succeeded
        $response->assertStatus(201);
        // Make sure that the customer was charged the correct amount
        $this->assertEquals(9750, $this->paymentGateway->totalCharges());
        // Make sure that an order exists for this customer
        $order = $concert->orders()->where('email', 'ahmed@example.com')->first();
        $this->assertNotNull($order);
        $this->assertEquals(3, $order->tickets->count());
    }

    /** @test */
    function cannot_purchase_tickets_to_an_unpublished_concert()
    {
        $concert = factory(Concert::class)->state('unpublished')->create();

        $response = $this->json('POST', route('concerts.orders', $concert->id), [
            'email' => 'ahmed@example.com',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken()
        ]);

        $response->assertStatus(404);
        $this->assertEquals(0, $this->paymentGateway->totalCharges());
        $this->assertDatabaseMissing('orders', ['concert_id' => $concert->id]);
    }

    /** @test */
    function an_order_is_not_created_if_payment_fails()
    {
        $concert = factory(Concert::class)->state('published')->create([
            'ticket_price' => 3250
        ]);

        $response = $this->json('POST', route('concerts.orders', $concert->id), [
            'email' => 'ahmed@example.com',
            'ticket_quantity' => 3,
            'payment_token' => 'invalid-payment-token'
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseMissing('orders', ['concert_id' => $concert->id]);
    }

    /** @test */
    function email_is_required_to_purchase_tickets()
    {
        $concert = factory(Concert::class)->state('published')->create();

        $response = $this->json('POST', route('concerts.orders', $concert->id), [
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken()
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('email');
    }

    /** @test */
    function email_must_be_valid_to_purchase_tickets()
    {
        $concert = factory(Concert::class)->state('published')->create();

        $response = $this->json('POST', route('concerts.orders', $concert->id), [
            'email' => 'not-valid-email-address',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken()
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('email');
    }

    /** @test */
    function ticket_quantity_must_be_at_least_1_to_purchase_tickets()
    {
        $concert = factory(Concert::class)->state('published')->create();

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
        $concert = factory(Concert::class)->state('published')->create();

        $response = $this->json('POST', route('concerts.orders', $concert->id), [
            'email' => 'ahmed@example.com',
            'ticket_quantity' => 3,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('payment_token');
    }
}
