<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
| AmeelHub — Step 1.5 Rate Limiters Applied
|--------------------------------------------------------------------------
*/

// -----------------------------------------------------------------------
// GENERAL API — throttle:api (60/min per IP)
// -----------------------------------------------------------------------
Route::middleware('throttle:api')->group(function () {

    // -----------------------------------------------------------------------
    // FUTURE: AI Job Match (Phase 11 — Step 11.3)
    // -----------------------------------------------------------------------
    // Route::middleware('auth:sanctum')->group(function () {
    //     Route::get('/jobs/recommended', [JobMatchController::class, 'index'])
    //         ->name('api.jobs.recommended');
    // });

    // -----------------------------------------------------------------------
    // FUTURE: PWA Push Notifications (Phase 11 — Step 11.1)
    // -----------------------------------------------------------------------
    // Route::middleware('auth:sanctum')->group(function () {
    //     Route::post('/push/subscribe', [PushSubscriptionController::class, 'store'])
    //         ->name('api.push.subscribe');
    //     Route::delete('/push/unsubscribe', [PushSubscriptionController::class, 'destroy'])
    //         ->name('api.push.unsubscribe');
    // });

    // -----------------------------------------------------------------------
    // FUTURE: WhatsApp Alerts Webhook (Phase 11 — Step 11.2)
    // -----------------------------------------------------------------------
    // Route::post('/webhook/ultramsg', [UltraMsgWebhookController::class, 'handle'])
    //     ->name('api.webhook.ultramsg');

});