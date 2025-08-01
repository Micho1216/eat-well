<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\VendorReview;
use App\Observers\OrderObserver;
use App\Observers\VendorReviewObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
        VendorReview::observe(VendorReviewObserver::class);
        Order::observe(OrderObserver::class);
    }
}
