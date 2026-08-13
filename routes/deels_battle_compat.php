<?php

declare(strict_types=1);

use App\Http\Controllers\Api\DeelsBattleCompatibilityController;
use Illuminate\Support\Facades\Route;

Route::get('battles/{id}', [DeelsBattleCompatibilityController::class, 'show'])
    ->whereNumber('id')
    ->name('deels.compat.battles.show');

Route::middleware(['auth:sanctum', 'update.user.data'])->group(function (): void {
    Route::post('battles', [DeelsBattleCompatibilityController::class, 'store'])
        ->name('deels.compat.battles.store');
    Route::put('battles/{id}', [DeelsBattleCompatibilityController::class, 'update'])
        ->whereNumber('id')
        ->name('deels.compat.battles.update');
    Route::post('battles/{id}/response', [DeelsBattleCompatibilityController::class, 'response'])
        ->whereNumber('id')
        ->name('deels.compat.battles.response');
});
