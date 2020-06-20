<?php

namespace App;

use App\Exceptions\NotEnoughTicketsException;
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

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
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
        if ($this->tickets()->available()->count() < $ticket_quantity) {
            throw new NotEnoughTicketsException;
        }

        $order = $this->orders()->create(['email' => $email]);

        $this->tickets()->available()->take($ticket_quantity)->update([
            'order_id' => $order->id
        ]);

        return $order;
    }

    public function addTickets($quantity)
    {
        $this->tickets()
            ->insert(
                $this->tickets()
                    ->makeMany(collect()->pad($quantity, []))
                    ->toArray()
            );

        return $this;
    }

    public function ticketsRemaining()
    {
        return $this->tickets()->whereNull('order_id')->count();
    }

    public function orderFor($customer_email)
    {
        return $this->orders()->where('email', $customer_email);
    }

    public function hasOrderFor($customer_email)
    {
        return $this->orderFor($customer_email)->exists();
    }
}
