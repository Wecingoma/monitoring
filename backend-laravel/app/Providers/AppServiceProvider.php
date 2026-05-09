<?php

namespace App\Providers;

use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return $request->user()
                ? RateLimiter::perMinute(120)->by($request->user()->id)
                : RateLimiter::perMinute(60)->by($request->ip());
        });
    }
}