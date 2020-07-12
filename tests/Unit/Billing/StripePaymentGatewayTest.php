<?php

namespace Tests\Unit\Billing;

use App\Billing\PaymentFailedException;
use App\Billing\StripePaymentGateway;
use Stripe\Charge;
use Stripe\Token;
use Tests\TestCase;

/**
 * @group integration
 * Class StripePaymentGatewayTest
 * @package Tests\Unit\Billing
 */
class StripePaymentGatewayTest extends TestCase
{
    /**
     * @var mixed
     */
    private $lastCharge;

    protected function setUp(): void
    {
        parent::setUp();
        $this->lastCharge = $this->lastCharge();
    }

    /** @test */
    function charges_with_a_valid_payment_token_are_successful()
    {
        $paymentGateway = new StripePaymentGateway(config('services.stripe.key'));

        $paymentGateway->charge(2500, $this->validToken());

        $this->assertCount(1, $this->newCharges());
        $this->assertEquals(2500, $this->lastCharge()->amount);
    }

    /** @test */
    function charges_with_an_invalid_payment_token_fail()
    {
        try {
            $paymentGateway = new StripePaymentGateway(config('services.stripe.key'));
            $paymentGateway->charge(2500, 'invalid-payment_token');
        } catch (PaymentFailedException $e) {
            $this->assertCount(0, $this->newCharges());
            return;
        }

        $this->fail('Charging with an invalid payment token did not throw a PaymentFailedException.');
    }

    /**
     * @return mixed
     * @throws \Stripe\Exception\ApiErrorException
     */
    private function lastCharge()
    {
        return Charge::all([
            'limit' => 1
        ], [
            'api_key' => config('services.stripe.key')
        ])['data'][0];
    }

    /**
     * @return string
     * @throws \Stripe\Exception\ApiErrorException
     */
    private function validToken(): string
    {
        return Token::create([
            'card' => [
                'number' => '4242424242424242',
                'exp_month' => 1,
                'exp_year' => date('Y') + 1,
                'cvc' => '123',
            ]
        ], [
            'api_key' => config('services.stripe.key')
        ])->id;
    }

    /**
     * @return mixed
     * @throws \Stripe\Exception\ApiErrorException
     */
    private function newCharges()
    {
        return Charge::all([
            'limit' => 1,
            'ending_before' => $this->lastCharge->id
        ], [
            'api_key' => config('services.stripe.key')
        ])['data'];
    }
}
