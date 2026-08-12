<?php

declare(strict_types=1);

use App\Http\Controllers\Api\DeelsCompatibilityController;
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
    Route::get('wallet', [DeelsCompatibilityController::class, 'wallet'])
        ->name('deels.compat.wallet');
    Route::get('messages/dialogs', [DeelsCompatibilityController::class, 'dialogs'])
        ->name('deels.compat.messages.dialogs');
    Route::get('messages/dialogs/{id}', [DeelsCompatibilityController::class, 'thread'])
        ->whereNumber('id')
        ->name('deels.compat.messages.thread');
});
