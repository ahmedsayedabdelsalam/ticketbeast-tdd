<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Concert
 * @package App
 * @property $date
 * @method static published()
 */
class Concert extends Model
{
    protected $guarded = [];

    protected $dates = [
        'date',
        'published_at'
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function getFormattedDateAttribute()
    {
        return $this->date->format('F j, Y');
    }

    public function getFormattedStartTimeAttribute()
    {
        return $this->date->format('g:i a');
    }

    public function getPriceInDollarsAttribute()
    {
        return number_format($this->ticket_price / 100, 2);
    }

    public function scopePublished(Builder $query)
    {
        return $query->whereNotNull('published_at');
    }

    public function orderTickets(string $email, int $ticket_quantity)
    {
        $order = $this->orders()->create(['email' => $email]);

        $order->tickets()->insert($order->tickets()->makeMany(collect()->pad($ticket_quantity, []))->toArray());

        return $order;
    }
}
