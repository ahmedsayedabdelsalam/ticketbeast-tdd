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

    public function cancel()
    {
        $this->tickets()->release();

        $this->delete();
    }

    public function ticketQuantity()
    {
        return $this->tickets()->count();
    }
}
