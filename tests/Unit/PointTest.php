<?php

namespace Tests\Unit;


use App\Exceptions\PointConsumption;
use App\Point;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PointTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function can_add_points_to_a_user()
    {
        $user = factory(User::class)->create();

        (new Point)->addPoints($user->id, $amount = 20, $expiration_date = Carbon::now()->addDays(30));

        $this->assertDatabaseHas('points', [
            'user_id' => $user->id,
            'initial_amount' => $amount,
            'remaining_amount' => $amount,
            'consumption_amount' => 0,
            'expired_at' => $expiration_date
        ]);
    }

    /** @test */
    function cat_get_total_points_for_a_user_that_not_expired()
    {
        $user = factory(User::class)->create();

        (new Point)->addPoints($user->id, 20, Carbon::now()->addDays(30));
        (new Point)->addPoints($user->id, 10, Carbon::now()->addDays(20));
        (new Point)->addPoints($user->id, 40, Carbon::now()->subDays(30));

        $remaining_amount = (new Point)->getTotalValidPoints($user->id);

        $this->assertEquals(30, $remaining_amount);
    }

    /** @test */
    function thorws_exception_if_user_want_to_consume_points_which_his_balance_can_not_cover()
    {
        $this->expectException(PointConsumption::class);

        $user = factory(User::class)->create();

        (new Point)->addPoints($user->id, 20, Carbon::now()->addDays(30));
        (new Point)->addPoints($user->id, 10, Carbon::now()->addDays(20));
        (new Point)->addPoints($user->id, 40, Carbon::now()->subDays(30));

        (new Point)->consumePoints($user->id, 50);
    }

    /** @test */
    function user_can_consume_points_if_his_balance_covers_the_consumption_amount_old_points_first()
    {
        $user = factory(User::class)->create();
        $another_user = factory(User::class)->create();

        (new Point)->addPoints($user->id, 20, Carbon::now()->addDays(30));
        (new Point)->addPoints($user->id, 10, Carbon::now()->addDays(20));
        (new Point)->addPoints($user->id, 40, Carbon::now()->subDays(30));
        (new Point)->addPoints($another_user->id, 50, Carbon::now()->addDays(30));

        (new Point)->consumePoints($user->id, 15);


        $remaining_amount = (new Point)->getTotalValidPoints($user->id);

        $this->assertEquals(15, $remaining_amount);
    }
}
