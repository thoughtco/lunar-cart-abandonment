<?php

namespace Thoughtco\LunarCartAbandonment;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

class ServiceProvider extends LaravelServiceProvider
{    
    public function boot()
    {
        $this->mergeConfigFrom($config = __DIR__.'/../config/cart_abandonment.php', 'lunar.cart_abandonment');
        
        $this->publishes([$config => config_path('lunar/cart_abandonment.php')], 'lunar-cart-abandonment-config');

        $this->commands([
            Commands\CartAbandonment::class,
        ]);
    
        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule->command('lunar:cart-abandonment:run')->cron('*/'.config('lunar.cart_abandonment.schedule_interval', 5).' * * * *');
        });
    }
}
