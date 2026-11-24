<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->throttleApi('api');
        $middleware->alias([
            'lang' => \App\Http\Middleware\SetLang::class,
            'authCheck' => \App\Http\Middleware\authCheck::class,
            'price_mode' => \App\Http\Middleware\PriceModeMiddleware::class,
            'EmailVerfived' => \App\Http\Middleware\EmailVerfived::class,
            'AdminCheck' => \App\Http\Middleware\AdminCheck::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
