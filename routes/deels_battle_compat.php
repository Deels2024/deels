<?php

declare(strict_types=1);

use App\Http\Controllers\Api\DeelsBattleCompatibilityController;
use Illuminate\Support\Facades\Route;

Route::get('battles/{id}', [DeelsBattleCompatibilityController::class, 'show'])
    ->whereNumber('id')
    ->name('deels.compat.battles.show');
