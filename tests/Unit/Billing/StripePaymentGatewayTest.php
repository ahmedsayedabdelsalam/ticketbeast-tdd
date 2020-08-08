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
    use PaymentGatewayContractTests;

    public function getPaymentGateway()
    {
        return new StripePaymentGateway(config('services.stripe.key'));
    }

}
