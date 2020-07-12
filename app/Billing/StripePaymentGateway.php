<?php

namespace App\Billing;


use Stripe\Charge;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\InvalidRequestException;

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
}
