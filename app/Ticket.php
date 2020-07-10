<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = ['reserved_at'];

    public function concert()
    {
        return $this->belongsTo(Concert::class);
    }

    public function scopeAvailable(Builder $query)
    {
        return $query->whereNull('order_id')->whereNull('reserved_at');
    }

    public function scopeRelease(Builder $query)
    {
        return $query->update([
            'reserved_at' => null
        ]);
    }

    public function scopeReserve(Builder $query)
    {
        return $query->update([
            'reserved_at' => Carbon::now()
        ]);
    }

    public function release()
    {
        return $this->update([
            'reserved_at' => null
        ]);
    }

    public function reserve()
    {
        return $this->update([
            'reserved_at' => Carbon::now()
        ]);
    }

    public function getPriceAttribute(){
        return $this->concert->ticket_price;
    }
}
