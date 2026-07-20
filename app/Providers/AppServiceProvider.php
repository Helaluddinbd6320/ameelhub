<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Worker;
use App\Observers\UserObserver;
use App\Observers\WorkerObserver;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
            $this->app->singleton(\App\Services\CvApprovalService::class);

    }

    public function boot(): void
    {
                User::observe(UserObserver::class);
                Worker::observe(WorkerObserver::class);

        // Security Layer — Super Admin bypasses all policy checks
        Gate::before(function ($user, $ability) {
            return $user->hasRole('super_admin') ? true : null;
        });

        $this->configureRateLimiters();
    }

    protected function configureRateLimiters(): void
    {
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)
                ->by($request->ip())
                ->response(function () {
                    return response()->json([
                        'message' => 'অনেক বেশি চেষ্টা করা হয়েছে। ১ মিনিট পর আবার চেষ্টা করুন।',
                    ], 429);
                });
        });

        RateLimiter::for('social-auth', function (Request $request) {
            return Limit::perMinute(10)
                ->by($request->ip());
        });

        RateLimiter::for('cv-reveal', function (Request $request) {
            return Limit::perDay(10)
                ->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('fee-reveal', function (Request $request) {
            return Limit::perDay(20)
                ->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('nok', function (Request $request) {
            return Limit::perMinute(10)
                ->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('dispute', function (Request $request) {
            return Limit::perDay(2)
                ->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('withdrawal', function (Request $request) {
            return Limit::perDay(3)
                ->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)
                ->by($request->user()?->id ?: $request->ip());
        });
    }
}