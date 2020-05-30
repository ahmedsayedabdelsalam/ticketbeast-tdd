<?php


namespace App\Billing;


interface PaymentGateway
{

    public function charge(int $amount, string $token);
}
