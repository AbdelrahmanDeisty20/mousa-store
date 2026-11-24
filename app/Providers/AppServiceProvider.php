<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{App, DB, RateLimiter};
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
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        DB::listen(function ($query) {
            logger()->info('SQL', [
                'sql' => $query->sql,
                'bindings' => $query->bindings,
                'time' => $query->time,
            ]);
        });

        $locale = explode(',', request()->header('Accept-Language', config('app.locale')))[0];
        $locale = explode('-', $locale)[0];

        App::setLocale($locale);
        if (! $this->app->runningInConsole()) {
            app()->setLocale(session('lang', default: config('app.locale')));
        }
    }
}
