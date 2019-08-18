<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Observers\BookingObserver;
use App\Models\Booking;
class BookingModelServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
         Booking::observe(BookingObserver::class);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
