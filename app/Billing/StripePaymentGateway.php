<?php

namespace App\Billing;


use Stripe\Charge;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\InvalidRequestException;
use Stripe\Token;

class StripePaymentGateway implements PaymentGateway
{
    private $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function charge(int $amount, string $token)
    {
        try {
            Charge::create([
                "amount" => $amount,
                "source" => $token,
                "currency" => "usd",
            ], [
                "api_key" => $this->apiKey,
            ]);
        } catch (InvalidRequestException $e) {
            throw new PaymentFailedException;
        }
    }

    /**
     * @return string
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function getValidTestToken(): string
    {
        return Token::create([
            'card' => [
                'number' => '4242424242424242',
                'exp_month' => 1,
                'exp_year' => date('Y') + 1,
                'cvc' => '123',
            ]
        ], [
            'api_key' => $this->apiKey
        ])->id;
    }

    public function newChargesDuring($callback)
    {
        $latestCharge = $this->lastCharge();
        $callback($this);
        return $this->newChargesSince($latestCharge)->pluck('amount');
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
            'api_key' => $this->apiKey
        ])['data'][0];
    }

    /**
     * @param null $charge
     * @return mixed
     * @throws ApiErrorException
     */
    private function newChargesSince($charge = null)
    {
        $charges = Charge::all([
            'ending_before' => $charge ? $charge->id : null
        ], [
            'api_key' => $this->apiKey
        ])['data'];

        return collect($charges);
    }
}
