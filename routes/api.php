<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Configuration\BannerController;
use App\Http\Controllers\Api\V1\Configuration\BirthdayController;
use App\Http\Controllers\Api\V1\Configuration\EventTypeController;
use App\Http\Controllers\Api\V1\Configuration\EventController;
use App\Http\Controllers\Api\V1\Configuration\LiturgyController;
use App\Http\Controllers\Api\V1\Configuration\MinistryController;
use App\Http\Controllers\Api\V1\Configuration\PollController;
use App\Http\Controllers\Api\V1\Configuration\ReflectionController;
use App\Http\Controllers\Api\V1\Configuration\SocialMediaController;
use App\Http\Controllers\Api\V1\Configuration\TeamMemberController;
use App\Http\Controllers\Api\V1\ContactController;
use App\Http\Controllers\Api\V1\PetitionController;

Route::middleware('api.key')->group(function () {
    Route::prefix('v1')->group(function () {
        Route::prefix('configuration')->group(function () {
            Route::get('events-home', [EventController::class, 'eventsHome']);
            Route::get('events/filter', [EventController::class, 'filter']);
            Route::get('events/active-months', [EventController::class, 'getActiveMonths']);
            Route::get('events/{slug}', [EventController::class, 'showBySlug']);
            Route::get('event-types', [EventTypeController::class, 'index']);
            Route::get('banners', [BannerController::class, 'index']);
            Route::get('banners/{section}', [BannerController::class, 'bySection']);
            Route::get('ministries', [MinistryController::class, 'index']);
            Route::get('polls', [PollController::class, 'index']);
            Route::get('birthdays', [BirthdayController::class, 'index']);
            Route::get('liturgy/today', [LiturgyController::class, 'today']);
            Route::get('social-media', [SocialMediaController::class, 'index']);
            Route::get('servants', [TeamMemberController::class, 'index']);
            Route::get('team-members', [TeamMemberController::class, 'index']);
            Route::get('reflections', [ReflectionController::class, 'index']);
            Route::get('reflections/{slug}', [ReflectionController::class, 'showBySlug']);
            Route::get('readings', [ReflectionController::class, 'index']);
            Route::get('readings/{slug}', [ReflectionController::class, 'showBySlug']);
        });

        Route::get('polls/lookup-assistant', [PollController::class, 'lookupAssistant'])->middleware('throttle:30,1');
        Route::post('polls/{poll}/vote', [PollController::class, 'vote'])->middleware('throttle:15,1');
        Route::post('polls/{poll}/register', [PollController::class, 'register'])->middleware('throttle:15,1');
        Route::post('contacts', [ContactController::class, 'store']);
        Route::post('petitions', [PetitionController::class, 'store']);
    });
});
