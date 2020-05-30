<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Support\ServiceProvider;

class EloquentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        HasOneOrMany::macro('makeMany', function(iterable $records) {
            $instances = $this->related->newCollection();

            foreach ($records as $record) {
                $instances->push($this->make($record));
            }

            return $instances;
        });
    }
}
