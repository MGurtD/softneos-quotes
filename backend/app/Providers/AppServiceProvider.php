<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

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
        $this->configureLoginRateLimit();
    }

    /**
     * 5 login attempts per minute per email + IP. Keyed by both so an
     * attacker cannot lock out a whole office behind one IP, nor try
     * unlimited passwords for one account by rotating IPs.
     */
    private function configureLoginRateLimit(): void
    {
        RateLimiter::for('login', function (Request $request): Limit {
            return Limit::perMinute(5)
                ->by(Str::lower((string) $request->input('email')).'|'.$request->ip())
                ->response(function (Request $request, array $headers) {
                    return back()
                        ->withErrors(['email' => __('auth.throttle', ['seconds' => $headers['Retry-After'] ?? 60])])
                        ->onlyInput('email');
                });
        });
    }
}
