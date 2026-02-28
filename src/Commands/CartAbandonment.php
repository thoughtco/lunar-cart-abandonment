<?php

namespace Thoughtco\LunarCartAbandonment\Commands;

use Illuminate\Console\Command;
use Lunar\Models\Cart;

class CartAbandonment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lunar:cart-abandonment:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Trigger cart abandonment emails';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (config('lunar.cart_abandonment.enabled', false)) {
            return 0;
        }

        $this->info('Beginning cart abandonment checks');

        $triggers = config('lunar.cart_abandonment.triggers', []);
        $channels = config('lunar.cart_abandonment.channels', ['*']);
        
        if (is_callable($triggers)) {
            $triggers = call_user_func($triggers, $channels);
        }
        
        if (! $triggers) {
            return;
        }
        
        $now = now();
        $maxIntervalAgo = 0;
        foreach ($triggers as $index => $trigger) {
            $triggers[$index]['interval'] *= 60;
            $triggers[$index]['interval_begin'] = $triggers[$index]['interval'] + (config('lunar.cart_abandonment.schedule_interval', 5) * 60) - 1;

            if ($triggers[$index]['interval_begin'] > $maxIntervalAgo) {
                $maxIntervalAgo = $triggers[$index]['interval_begin'];
            }
        }
                
        Cart::query()
            ->active()
            ->unmerged()
            ->when(! in_array('*', $channels), fn ($query) => $query->whereIn('channel_id', $channels))
            ->whereHas('lines', fn ($query) => $query->where('updated_at', '>=', $now()->clone()->subSeconds($maxIntervalAgo)))
            ->get()
            ->map(function ($cart) use ($now, $triggers) {
                $latestCartLine = $cart->lines->sortByDesc('updated_at')->first()?->updated_at ?? now()->subDays(30);

                $dateToUse = $latestCartLine->isAfter($cart->updated_at) ? $latestCartLine : $cart->updated_at;
                
                $inactivityInterval = $dateToUse->diffInSeconds($now);
                                                                
                foreach ($triggers as $trigger) {
                                        
                    if ($inactivityInterval <= $trigger['interval_begin'] && $inactivityInterval >= $trigger['interval']) {
                        $job = $trigger['job']::dispatch($cart, $trigger['config'] ?? []);
                        
                        if ($queue = $trigger['queue'] ?? false) {
                            $job->onQueue($queue);
                        }
                        
                        if ($connection = $trigger['queue_connection'] ?? false) {
                            $job->onConnection($connection);
                        }                        
                    }        
                }
                
            });
            
        $this->info('Complete');
            
        return 0;
    }
}
