<?php

return [
    
    'channels' => explode(',', env('LUNAR_CART_ABANDONMENT_CHANNELS', '*')),
    
    'schedule_interval' => 5, // how often to run the task

    'triggers' => [
        // [
        //     'interval' => 5, // minutes
        //     'job' => \App\Jobs\CartAbandonment::class,
        //     'queue' => 'default', // optional
        //     'queue_connection' => 'redis', // optional
        //     'config' => [],
        //
        // ],  
    ],

];
