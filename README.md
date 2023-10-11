## Lunar Cart Abandonment

An simple add-on for Lunar that triggers jobs based on how long a cart has been inactive for.

## Installation

First, require as a composer dependency:

```
composer require thoughtco/lunar-cart-abandonment
```

Then publish the config file to allow you to define triggers and jobs.

```
php artisan vendor:publish --tag=lunar-cart-abandonment-config
```


## Usage

You define triggers by adding new arrays to the `triggers` key in the config file, eg:

```php
        [
            'interval' => 5, // minutes
            'job' => \App\Jobs\CartAbandonment::class,
            'queue' => 'default', // optional
            'queue_connection' => 'redis', // optional
            'config' => [], // this will be passed to your job along with the cart
        ],  
```

Your job should expect 2 arguments, `$cart` and `$config`.

If you don't want the scheduled task to run every 5 minutes, you can change the frequency by using the `schedule_interval` config setting.


## Support

This is a free addon so support is provided on an as-we-have-capacity basis. If you have a feature request or experience a bug, please [open a GitHub Issue](https://github.com/thoughtco/lunar-cart-abandonment/issues).

Only the latest version of this addon is supported. If you open a bug report using an old version, your issue will be closed.
