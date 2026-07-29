<?php

namespace App\Providers;

use App\Auth\ArquanziaGuard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Auth::extend('arquanzia', function ($app, $name, array $config) {
            $guard = new ArquanziaGuard(
                $name,
                Auth::createUserProvider($config['provider']),
                $app['session.store'],
            );

            $guard->setCookieJar($app['cookie']);
            $guard->setDispatcher($app['events']);
            $guard->setRequest($app->refresh('request', $guard, 'setRequest'));

            return $guard;
        });
    }
}
