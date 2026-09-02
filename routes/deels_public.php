<?php

declare(strict_types=1);

use App\Http\Controllers\Api\BattleController;
use App\Http\Controllers\Api\ChallengeController;
use App\Http\Controllers\CampaignsController;
use App\Http\Controllers\DeelsPublicController;
use Illuminate\Support\Facades\Route;

Route::get('challenges/{id}', [ChallengeController::class, 'show'])
    ->whereNumber('id')
    ->name('deels.public.challenges.show');

Route::get('battles', [DeelsPublicController::class, 'battles'])
    ->name('deels.public.battles.index');
Route::get('battles/{id}', [BattleController::class, 'show'])
    ->whereNumber('id')
    ->name('deels.public.battles.show');

Route::get('stories/{id}', [DeelsPublicController::class, 'story'])
    ->whereNumber('id')
    ->name('deels.public.stories.show');

Route::get('campaigns', [CampaignsController::class, 'index'])
    ->name('deels.public.campaigns.index');
Route::get('campaigns/{slug}', [CampaignsController::class, 'show'])
    ->where('slug', '[A-Za-z0-9_-]+')
    ->name('deels.public.campaigns.show');
