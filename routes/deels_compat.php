<?php

declare(strict_types=1);

use App\Http\Controllers\Api\DeelsCompatibilityController;
use App\Http\Controllers\Api\DeelsContentCompatibilityController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Deels new-design API compatibility routes
|--------------------------------------------------------------------------
|
| Thin aliases/adapters for the REST contract used by Deels2024/deelsweb.
| Existing legacy routes and business controllers remain unchanged.
|
*/

Route::post('auth/login', [DeelsCompatibilityController::class, 'login'])
    ->name('deels.compat.auth.login');
Route::post('auth/register', [DeelsCompatibilityController::class, 'register'])
    ->middleware('throttle:registrations')
    ->name('deels.compat.auth.register');

Route::get('stats', [DeelsCompatibilityController::class, 'stats'])
    ->name('deels.compat.stats');
Route::get('feed', [DeelsCompatibilityController::class, 'feed'])
    ->name('deels.compat.feed');
Route::get('stories', [DeelsCompatibilityController::class, 'stories'])
    ->name('deels.compat.stories.index');
Route::get('stories/{id}', [DeelsCompatibilityController::class, 'story'])
    ->whereNumber('id')
    ->name('deels.compat.stories.show');
Route::get('challenges/{id}', [DeelsCompatibilityController::class, 'challenge'])
    ->whereNumber('id')
    ->name('deels.compat.challenges.show');

Route::middleware(['auth:sanctum', 'update.user.data'])->group(function (): void {
    Route::post('auth/logout', [DeelsCompatibilityController::class, 'logout'])
        ->name('deels.compat.auth.logout');

    Route::post('challenges', [DeelsContentCompatibilityController::class, 'createChallenge'])
        ->name('deels.compat.challenges.store');
    Route::put('challenges/{id}', [DeelsContentCompatibilityController::class, 'updateChallenge'])
        ->whereNumber('id')
        ->name('deels.compat.challenges.update');
    Route::post('challenges/{id}/responses', [DeelsContentCompatibilityController::class, 'joinChallenge'])
        ->whereNumber('id')
        ->name('deels.compat.challenges.responses.store');
    Route::post('stories', [DeelsContentCompatibilityController::class, 'createStory'])
        ->name('deels.compat.stories.store');

    Route::get('wallet', [DeelsCompatibilityController::class, 'wallet'])
        ->name('deels.compat.wallet');
    Route::get('messages/dialogs', [DeelsCompatibilityController::class, 'dialogs'])
        ->name('deels.compat.messages.dialogs');
    Route::get('messages/dialogs/{id}', [DeelsCompatibilityController::class, 'thread'])
        ->whereNumber('id')
        ->name('deels.compat.messages.thread');
});
