<?php

namespace App;

use App\Exceptions\PointConsumption;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Point extends Model
{
    protected $fillable = [
        'user_id',
        'initial_amount',
        'remaining_amount',
        'consumption_amount',
        'expired_at',
    ];

    protected $casts = [
        'initial_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'consumption_amount' => 'decimal:2',
    ];

    protected $dates = ['expired_at'];

    public function addPoints(int $user_id, $amount, $expiration_date = null)
    {
        return self::create([
            'user_id' => $user_id,
            'initial_amount' => $amount,
            'remaining_amount' => $amount,
            'expired_at' => $expiration_date
        ]);
    }

    public function consumePoints(int $user_id, $amount)
    {
        DB::transaction(function () use ($user_id, $amount) {
            // check if user has valid points that covers his consumption
            if ($amount > $this->getTotalValidPoints($user_id)) {
                throw new PointConsumption('balance can not cover the consumption amount');
            }

            // consume all the amounts nearest to expire at one which summation covers the consumption amount
            $points = self::nearestAmountToExpireForUser($amount, $user_id)->get();
            if ($points->count()) {
                $points->toQuery()
                    ->update([
                        'remaining_amount' => 0,
                        'consumption_amount' => DB::raw("`initial_amount`")
                    ]);
            }
            $remaining_points_after_consumption = $amount - $points->sum('remaining_amount');

            // if there are some remaining points after consuming nearest to expire points that covers the consumption amount
            // consume the remaining points from the next nearest to expire record
            if ($remaining_points_after_consumption > 0) {
                self::forUser($user_id)
                    ->notExpired()
                    ->hasSufficientAmount($remaining_points_after_consumption)
                    ->orderBy('expired_at')
                    ->take(1)
                    ->update([
                        'consumption_amount' => DB::raw("`consumption_amount` + $remaining_points_after_consumption"),
                        'remaining_amount' => DB::raw("`remaining_amount` - $remaining_points_after_consumption")
                    ]);
            }
        });
    }

    public function scopeHasSufficientAmount(Builder $query, $amount = 1)
    {
        return $query->where('remaining_amount', '>=', $amount);
    }

    public function getTotalValidPoints(int $user_id)
    {
        return self::forUser($user_id)
            ->notExpired()
            ->hasSufficientAmount()
            ->sum('remaining_amount');
    }

    public function scopeForUser(Builder $query, int $user_id)
    {
        return $query->where('user_id', $user_id);
    }

    public function scopeNotExpired(Builder $query)
    {
        return $query->whereDate('expired_at', '>=', Carbon::now());
    }

    public function scopeNearestAmountToExpireForUser(Builder $query, $amount, $user_id)
    {
        return $query->from(
            Point::selectRaw('*, sum(remaining_amount) OVER (ORDER BY expired_at ASC) AS total')
                ->forUser($user_id)
                ->notExpired()
                ->hasSufficientAmount()
                ->orderBy('expired_at'),
            'totals')
            ->where('total', '<', $amount);
    }
}
