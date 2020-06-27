<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $guarded = [];

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public static function forTickets($tickets, string $email, $amount)
    {
        $order = self::create([
            'email' => $email,
            'amount' => $amount
        ]);

        $tickets->toQuery()->update([
            'order_id' => $order->id
        ]);

        return $order;
    }

    public function cancel()
    {
        $this->tickets()->release();

        $this->delete();
    }

    public function ticketQuantity()
    {
        return $this->tickets()->count();
    }

    public function toArray()
    {
        return [
            'email' => $this->email,
            'ticket_quantity' => $this->ticketQuantity(),
            'amount' => $this->amount
        ];
    }
}
