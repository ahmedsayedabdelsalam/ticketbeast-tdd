<?php

namespace App\Http\Controllers;

use App\Billing\PaymentFailedException;
use App\Billing\PaymentGateway;
use App\Concert;
use App\Exceptions\NotEnoughTicketsException;
use App\Ticket;
use Illuminate\Http\Request;

class ConcertOrderController extends Controller
{
    private $paymentGateway;

    public function __construct(PaymentGateway $paymentGateway)
    {
        $this->paymentGateway = $paymentGateway;
    }

    public function store($concertId)
    {
        $concert = Concert::published()->findOrFail($concertId);

        $this->validate(request(), [
            'email' => ['required', 'email'],
            'ticket_quantity' => ['required', 'integer', 'min:1'],
            'payment_token' => ['required']
        ]);

        try {
            $order = $concert->orderTickets(request('email'), request('ticket_quantity'));
            $this->paymentGateway->charge(request('ticket_quantity') * $concert->ticket_price, request('payment_token'));

            return response()->json([], 201);
        } catch (PaymentFailedException $exception) {
            $order->cancel();
            return response()->json([], 422);
        } catch (NotEnoughTicketsException $exception) {
            return response()->json([], 422);
        }
    }
}
