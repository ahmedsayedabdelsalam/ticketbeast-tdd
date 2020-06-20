<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    public function scopeAvailable(Builder $query)
    {
        return $query->whereNull('order_id');
    }

    public function scopeRelease(Builder $query)
    {
        return $query->update([
            'order_id' => null
        ]);
    }

    public function release()
    {
        $this->order_id = null;
        return $this->update();
    }
}
