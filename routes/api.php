<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Configuration\EventTypeController;
use App\Http\Controllers\Api\V1\Configuration\EventController;

Route::middleware('api.key')->group(function () {
    Route::prefix('v1')->group(function () {
        Route::prefix('configuration')->group(function () {
            Route::get('events-home', [EventController::class, 'eventsHome']);
            Route::get('events/filter', [EventController::class, 'filter']);
            Route::get('events/active-months', [EventController::class, 'getActiveMonths']);
            Route::get('events/{slug}', [EventController::class, 'showBySlug']);
            Route::get('event-types', [EventTypeController::class, 'index']);
        });
    });
});
