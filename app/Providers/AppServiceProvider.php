<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        Collection::macro('toQuery', function () {

            $model = $this->first();

            if (!$model) {
                throw new LogicException('Collection is Empty');
            }

            return $model->newModelQuery()->whereKey($this->modelKeys());
        });
    }
}

///**
// * Get the Eloquent query builder from the collection
// *
// * @return Illuminate\Database\Eloquen\Builder
// *
// * @throws \LogicException
// */
//public function toQuery()
//{
//    $model = $this->first();
//
//    if (! $model) {
//        throw new LogicException('Collection is Empty');
//    }
//
//    return $model->newModelQuery()->whereKey($this->modelKeys());
//}
