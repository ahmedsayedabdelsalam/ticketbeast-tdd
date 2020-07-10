<?php

namespace App;

use App\Billing\PaymentGateway;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class Reservation
{
    /**
     * @var $tickets
     */
    protected $tickets;

    /**
     * @var $email
     */
    protected $email;

    public function __construct($tickets, string $email)
    {
        $this->tickets = $tickets;
        $this->email = $email;
    }

    public function totalCost()
    {
        return $this->tickets->sum('price');
    }

    public function tickets()
    {
        return $this->tickets;
    }

    public function email()
    {
        return $this->email;
    }

    public function complete(PaymentGateway $paymentGateway, $paymentToken)
    {
        $paymentGateway->charge($this->totalCost(), $paymentToken);

        return Order::forTickets($this->tickets(), $this->email(), $this->totalCost());
    }

    public function cancel()
    {
        foreach ($this->tickets as $ticket) {
            $ticket->release();
        }
    }
}
