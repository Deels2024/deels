<?php

declare(strict_types=1);

use App\Http\Controllers\Api\DeelsCampaignCompatibilityController;
use App\Http\Controllers\Api\DeelsCommunicationCompatibilityController;
use App\Http\Controllers\Api\DeelsCompatibilityController;
use App\Http\Controllers\Api\DeelsContentCompatibilityController;
use App\Http\Controllers\Api\DeelsEngagementCompatibilityController;
use App\Http\Controllers\Api\DeelsSettingsCompatibilityController;
use App\Http\Controllers\Api\DeelsSocialCompatibilityController;
use App\Http\Controllers\Api\DeelsUtilityCompatibilityController;
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
Route::post('auth/forgot-password', [DeelsUtilityCompatibilityController::class, 'forgotPassword'])
    ->name('deels.compat.auth.forgot-password');
Route::post('auth/reset-password', [DeelsUtilityCompatibilityController::class, 'resetPassword'])
    ->name('deels.compat.auth.reset-password');

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
Route::get('battles', [DeelsEngagementCompatibilityController::class, 'battles'])
    ->name('deels.compat.battles.index');

Route::get('campaigns', [DeelsCampaignCompatibilityController::class, 'index'])
    ->name('deels.compat.campaigns.index');
Route::get('campaigns/{id}', [DeelsCampaignCompatibilityController::class, 'show'])
    ->where('id', '[A-Za-z0-9_-]+')
    ->name('deels.compat.campaigns.show');

Route::get('search', [DeelsUtilityCompatibilityController::class, 'search'])
    ->name('deels.compat.search');
Route::post('contacts', [DeelsUtilityCompatibilityController::class, 'contact'])
    ->name('deels.compat.contacts.store');

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
    Route::post('challenges/{id}/save', [DeelsEngagementCompatibilityController::class, 'saveChallenge'])
        ->whereNumber('id')
        ->name('deels.compat.challenges.save');
    Route::post('challenge-responses/{id}/vote', [DeelsEngagementCompatibilityController::class, 'voteChallengeResponse'])
        ->whereNumber('id')
        ->middleware(['suspicious.restricted', 'action.limit.like'])
        ->name('deels.compat.challenge-responses.vote');
    Route::post('battles/{id}/vote', [DeelsEngagementCompatibilityController::class, 'voteBattle'])
        ->whereNumber('id')
        ->middleware(['suspicious.restricted', 'action.limit.like'])
        ->name('deels.compat.battles.vote');

    Route::post('stories', [DeelsContentCompatibilityController::class, 'createStory'])
        ->name('deels.compat.stories.store');
    Route::post('campaigns', [DeelsCampaignCompatibilityController::class, 'store'])
        ->name('deels.compat.campaigns.store');

    Route::get('profile', [DeelsSocialCompatibilityController::class, 'profile'])
        ->name('deels.compat.profile.show');
    Route::patch('profile', [DeelsSocialCompatibilityController::class, 'updateProfile'])
        ->name('deels.compat.profile.update');
    Route::post('profile/avatar', [DeelsSocialCompatibilityController::class, 'avatar'])
        ->name('deels.compat.profile.avatar');
    Route::get('users/{id}', [DeelsSocialCompatibilityController::class, 'user'])
        ->whereNumber('id')
        ->name('deels.compat.users.show');
    Route::get('users/{id}/content', [DeelsSocialCompatibilityController::class, 'userContent'])
        ->whereNumber('id')
        ->name('deels.compat.users.content');
    Route::post('users/{id}/follow', [DeelsSocialCompatibilityController::class, 'follow'])
        ->whereNumber('id')
        ->middleware('suspicious.restricted')
        ->name('deels.compat.users.follow');

    Route::post('{type}/{id}/like', [DeelsSocialCompatibilityController::class, 'like'])
        ->where('type', 'stories|story|challenges|challenge-responses')
        ->whereNumber('id')
        ->middleware(['suspicious.restricted', 'action.limit.like'])
        ->name('deels.compat.social.like');
    Route::delete('{type}/{id}/like', [DeelsSocialCompatibilityController::class, 'unlike'])
        ->where('type', 'stories|story|challenges|challenge-responses')
        ->whereNumber('id')
        ->middleware('suspicious.restricted')
        ->name('deels.compat.social.unlike');
    Route::post('{type}/{id}/comments', [DeelsSocialCompatibilityController::class, 'comment'])
        ->where('type', 'stories|story|challenges|challenge-responses')
        ->whereNumber('id')
        ->middleware('suspicious.restricted')
        ->name('deels.compat.social.comment');
    Route::post('{type}/{id}/share', [DeelsSocialCompatibilityController::class, 'share'])
        ->where('type', 'stories|story|challenges|challenge-responses')
        ->whereNumber('id')
        ->name('deels.compat.social.share');

    Route::post('media', [DeelsUtilityCompatibilityController::class, 'media'])
        ->name('deels.compat.media.store');

    Route::get('wallet', [DeelsCompatibilityController::class, 'wallet'])
        ->name('deels.compat.wallet');
    Route::get('messages/dialogs', [DeelsCompatibilityController::class, 'dialogs'])
        ->name('deels.compat.messages.dialogs');
    Route::get('messages/dialogs/{id}', [DeelsCompatibilityController::class, 'thread'])
        ->whereNumber('id')
        ->name('deels.compat.messages.thread');
    Route::post('messages/dialogs/{id}/messages', [DeelsCommunicationCompatibilityController::class, 'sendMessage'])
        ->whereNumber('id')
        ->middleware(['suspicious.restricted', 'action.limit.message'])
        ->name('deels.compat.messages.send');

    Route::get('notifications', [DeelsCommunicationCompatibilityController::class, 'notifications'])
        ->name('deels.compat.notifications.index');
    Route::post('notifications/{id}/read', [DeelsCommunicationCompatibilityController::class, 'readNotification'])
        ->whereNumber('id')
        ->name('deels.compat.notifications.read');
    Route::post('notifications/read-all', [DeelsCommunicationCompatibilityController::class, 'readAllNotifications'])
        ->name('deels.compat.notifications.read-all');

    Route::patch('settings/preferences', [DeelsSettingsCompatibilityController::class, 'preferences'])
        ->name('deels.compat.settings.preferences');
    Route::put('settings/password', [DeelsSettingsCompatibilityController::class, 'changePassword'])
        ->name('deels.compat.settings.password');
    Route::get('settings/sessions', [DeelsSettingsCompatibilityController::class, 'sessions'])
        ->name('deels.compat.settings.sessions');
    Route::post('settings/sessions/close-others', [DeelsSettingsCompatibilityController::class, 'closeOtherSessions'])
        ->name('deels.compat.settings.sessions.close-others');
});
