<?php

namespace Tests\Unit\Billing;

use App\Billing\FakePaymentGateway;
use App\Billing\PaymentFailedException;
use PHPUnit\Framework\TestCase;

class FakePaymentGatewayTest extends TestCase
{
    use PaymentGatewayContractTests;

    public function getPaymentGateway()
    {
        return new FakePaymentGateway;
    }

    /** @test */
    function running_a_hook_before_the_first_charge()
    {
        $paymentGateway = $this->getPaymentGateway();
        $timesCallbackRan = 0;

        $paymentGateway->beforeFirstCharge(function ($paymentGateway) use (&$timesCallbackRan) {
            $timesCallbackRan++;
            $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());
            $this->assertEquals(2500, $paymentGateway->totalCharges());
        });

        $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());

        $this->assertEquals(1, $timesCallbackRan);
        $this->assertEquals(5000, $paymentGateway->totalCharges());
    }
}
