<?php

use App\Http\Middleware\SanitizeInput;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        // SecurityHeaders → সব request এ চলবে (global)
        $middleware->append(SecurityHeaders::class);

        // SanitizeInput + SetLocale → web group এ যোগ করা হলো
        // Filament panel paths (/admin, /agent, /worker) middleware এর ভেতরে bypass হবে —
        // তাই AdminPanelProvider / AgentPanelProvider / WorkerPanelProvider এ
        // আলাদাভাবে ->middleware([SanitizeInput::class, SetLocale::class]) যোগ করতে হবে
        $middleware->web(append: [
            SetLocale::class,
            SanitizeInput::class,
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();