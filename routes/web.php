<?php

declare(strict_types=1);

use App\Http\Controllers\ActionsController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CampaignsController;
use App\Http\Controllers\PaymentCommentController;
use App\Http\Controllers\TelegramWebAppAuthController;
use App\Http\Controllers\UserController;
use App\Models\Mailing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', 'HomeController@index')->name('home');
Route::get('/home', 'HomeController@index');
Route::get('/testSocket', 'Api\SendSocketController@testSocket')->name('testSocket');
//Route::get('clear', 'HomeController@clearCache')->name('clear_cache');
Route::get('banned', 'HomeController@banned')->name('banned');
Route::group(['middleware' => 'auth'], function (): void {
    Route::get('/contests/{type}/{id}/invites/users', [\App\Http\Controllers\ContestInvitationController::class, 'users'])
        ->where('type', 'challenge|battle')
        ->name('contests.invites.users');
    Route::post('/contests/{type}/{id}/invites', [\App\Http\Controllers\ContestInvitationController::class, 'store'])
        ->where('type', 'challenge|battle')
        ->name('contests.invites.store');
//    Route::get('activation', 'UserController@activation')->name('activation');
    Route::post('/user/email', 'UserController@saveEmailAndSendCode')
        ->middleware('throttle:5,1')
        ->name('user.email.store');
    Route::post('/user/email/verify', 'UserController@verifyEmailCode')
        ->middleware('throttle:10,1')
        ->name('user.email.verify');
    Route::post('/user/email-prompt/postpone', 'UserController@postponeEmailPrompt')
        ->middleware('throttle:10,1')
        ->name('user.email-prompt.postpone');
    Route::post('/user/phone', 'UserController@savePhoneAndSendCode')
        ->middleware('throttle:5,1')
        ->name('user.phone.store');
    Route::post('/user/phone/resend', 'UserController@resendPhoneCode')
        ->middleware('throttle:5,1')
        ->name('user.phone.resend');
    Route::post('/user/phone/verify', 'UserController@verifyPhoneCode')
        ->middleware('throttle:10,1')
        ->name('user.phone.verify');
    Route::post('/user/phone-prompt/postpone', 'UserController@postponePhonePrompt')
        ->middleware('throttle:10,1')
        ->name('user.phone-prompt.postpone');
});

Route::get('/unsubscribe/{token?}', function ($token = null) {

    return 'Ваша заявка принята';
})->name('unsubscribe');

Route::post('/summernote/upload', [\App\Http\Controllers\SummernoteController::class, 'upload'])->name('summernote.upload');
Route::delete('/summernote/delete', [\App\Http\Controllers\SummernoteController::class, 'delete'])->name('summernote.delete');

Route::post('activation_verify', 'UserController@activation_verify')->name('activation_verify');
Route::get('logout', '\App\Http\Controllers\Auth\LoginController@logout');
Auth::routes();

Route::post('/user/sendEmailCode', 'UserController@sendCode');
Route::post('/user/checkEmailCode', [RegisterController::class, 'checkEmailCode'])
    ->middleware('throttle:10,1');
Route::post('/telegram/webapp/auth', [TelegramWebAppAuthController::class, 'authenticate'])->name('telegram.webapp.auth');
Route::get('/telegram/webapp/status', [TelegramWebAppAuthController::class, 'status'])->name('telegram.webapp.status');

Route::group(['prefix' => 'user'], function (): void {
    Route::post('/follow', ['as' => 'user.follow_toggle', 'uses' => 'FollowController@follow_toggle'])->middleware('suspicious.restricted');
    Route::post('/abuse', ['as' => 'user.abuse', 'uses' => 'AbuseController@abuse'])->middleware('suspicious.restricted');
});

//Route::get('/test', 'HomeController@test');
Route::get('/test_data', ['as' => 'test_data', 'uses' => 'TestController@index']);
//Route::get('/chat_test', ['as' => 'test_data', 'uses' => 'TestController@chat_test']);


Route::any('/callback/cb-payment', 'PaymentController@callbackPayment')->name('cb-payment');
Route::get('/onboarding', [\App\Http\Controllers\OnboardingController::class, 'index'])->name('onboarding');
Route::get('/onboarding_finish', [\App\Http\Controllers\OnboardingController::class, 'finish'])->name('onboarding_finish');
Route::group(['middleware' => 'auth'], function (): void {





    Route::any('add-to-cart/{reward_id?}', ['as' => 'add_to_cart', 'uses' => 'CampaignsController@addToCart']);

});
Route::controller(CampaignsController::class)->prefix('campaign')->group(function (): void {
    Route::get('/', 'index')->name('browse_campaign');
    Route::post('{slug}/addLike', 'addLike')->name('add_like')->middleware('suspicious.restricted');
    Route::post('donate', 'campaign_donate')->name('campaign_donate');
    Route::get('{slug}', 'show')->name('campaign_single');
    Route::get('backers/{slug}', 'showBackers')->name('campaign_backers');
    Route::get('updates/{slug}', 'showUpdates')->name('campaign_updates');
    Route::get('faqs/{slug}', 'showFaqs')->name('campaign_faqs');
});

//Route::get('campaigns', ['as' => 'browse_campaigns', 'uses' => 'CampaignsController@index']);
Route::get('campaigns/category/{slug}', ['as' => 'campaigns.category', 'uses' => 'CampaignsController@index']);
//Route::get('campaigns/filter', ['as' => 'browse_campaigns_filter', 'uses' => 'CampaignsController@browseCampaignsFilter']);
//Route::get('campaigns/projects-we-loved', ['as' => 'projects_we_loved', 'uses' => 'CampaignsController@projectsWeLoved']);
//Route::get(
//    'campaigns/funded-campaigns',
//    ['as' => 'recently_funded_campaigns', 'uses' => 'CampaignsController@recentlyFundedCampaigns']
//);

Route::get('contact-us', 'HomeController@contactUs')->name('contact_us');
Route::get('contacts', 'HomeController@contactUs')->name('contacts');


Route::post('contact-us', 'HomeController@contactUsPost')->name('contact_us_post');

Route::get('offer', 'HomeController@offer')->name('offer');
Route::get('/docs/card_pay', 'HomeController@cardPay')->name('card_pay');
Route::get('/docs/gift_offer', 'HomeController@giftOffer')->name('gift_offer');
Route::get('/docs/access_offer', 'HomeController@accessOffer')->name('access_offer');
Route::get('/docs/personal_offer', 'HomeController@personalOffer')->name('personal_offer');
Route::get('/docs/license', 'HomeController@license')->name('license');
Route::get('/docs/rules', 'HomeController@rules')->name('rules');

// categories

Route::group(['middleware' => 'auth'], function (): void {


// checkout
    Route::get('checkout', ['as' => 'checkout', 'uses' => 'CampaignsController@checkout']);
    Route::post('checkout', ['uses' => 'CampaignsController@checkoutPost']);

// Payment
    Route::post('checkout/paypal', ['as' => 'payment_paypal_receive', 'uses' => 'CampaignsController@paypalRedirect']);

    Route::any(
        'checkout/paypal-success/{transaction_id?}',
        ['as' => 'payment_success', 'uses' => 'CampaignsController@paymentSuccess']
    );
    Route::any('checkout/paypal-notify/{transaction_id?}', ['as' => 'paypal_notify', 'uses' => 'CampaignsController@paypalNotify']);

    Route::post('checkout/stripe', ['as' => 'payment_stripe_receive', 'uses' => 'CampaignsController@paymentStripeReceive']);
    Route::post(
        'checkout/bank-transfer',
        ['as' => 'bank_transfer_submit', 'uses' => 'CampaignsController@paymentBankTransferReceive']
    );
});

Route::get('search', ['as' => 'search', 'uses' => 'SearchController@search']);

Route::get('category/{slug}', ['as' => 'single_category', 'uses' => 'CategoriesController@singleCategory']);


// Cookie
Route::post('cookie-accept', ['as' => 'cookie_accept', 'uses' => 'HomeController@acceptCookie']);

// Comments
Route::post('post-comments/{id}', ['as' => 'post_comments', 'uses' => 'CommentController@postComments'])->middleware('suspicious.restricted');

Route::get('check-email-uniqueness', [RegisterController::class, 'checkEmailUniqueness']);
Route::get('check-username-uniqueness', [UserController::class, 'checkUsernameUniqueness']);

Route::group(['prefix' => 'login'], function (): void {
    // Social login route
    Route::get('facebook', ['as' => 'facebook_redirect', 'uses' => 'SocialLogin@redirectFacebook']);
    Route::get('facebook-callback', ['as' => 'facebook_callback', 'uses' => 'SocialLogin@callbackFacebook']);

    Route::get('vk', ['as' => 'vk_redirect', 'uses' => 'SocialLogin@redirectVK']);
    Route::get('vk-callback', ['as' => 'vk_callback', 'uses' => 'SocialLogin@callbackVK']);

    Route::get('yandex', ['as' => 'yandex_redirect', 'uses' => 'SocialLogin@redirectYandex']);
    Route::get('yandex-callback', ['as' => 'yandex_callback', 'uses' => 'SocialLogin@callbackYandex']);

    Route::get('mailru', ['as' => 'mailru_redirect', 'uses' => 'SocialLogin@redirectMailru']);
    Route::get('mailru-callback', ['as' => 'mailru_callback', 'uses' => 'SocialLogin@callbackMailru']);
    Route::get('mailru-app_callback', ['as' => 'mailru_callback', 'uses' => 'Api\AuthController@auth_mailru']);

    Route::get('ok', ['as' => 'ok_redirect', 'uses' => 'SocialLogin@redirectOK']);
    Route::get('ok-callback', ['as' => 'ok_callback', 'uses' => 'SocialLogin@callbackOK']);
    Route::get('ok-app_callback', ['as' => 'ok_callback', 'uses' => 'Api\AuthController@auth_ok']);

    Route::get('google', ['as' => 'google_redirect', 'uses' => 'SocialLogin@redirectGoogle']);
    Route::get('google-callback', ['as' => 'google_callback', 'uses' => 'SocialLogin@callbackGoogle']);

    Route::get('twitter', ['as' => 'twitter_redirect', 'uses' => 'SocialLogin@redirectTwitter']);
    Route::get('twitter-callback', ['as' => 'twitter_callback', 'uses' => 'SocialLogin@callbackTwitter']);
});

Route::group(['middleware' => 'auth'], function (): void {
    Route::post('payment-comment', [PaymentCommentController::class, 'comment'])->name('payment_comment');
    Route::post('/payments/{payment}/thank', [PaymentCommentController::class, 'thank'])->name('payment_thank');
    Route::post('/payments/thank/moderate', [PaymentCommentController::class, 'moderate'])->name('moderate_thank');
    Route::post('/contests/{type}/{id}/leave', [\App\Http\Controllers\ContestParticipationController::class, 'leave'])
        ->where('type', 'challenge|battle')
        ->name('contests.participation.leave');
    Route::post('/contests/{type}/{id}/rejoin', [\App\Http\Controllers\ContestParticipationController::class, 'rejoin'])
        ->where('type', 'challenge|battle')
        ->name('contests.participation.rejoin');
    Route::post('/contests/{type}/{id}/join', [\App\Http\Controllers\ContestParticipationController::class, 'join'])
        ->where('type', 'challenge|battle')
        ->name('contests.participation.join');
    Route::post('/battles/{id}/accept', [\App\Http\Controllers\ContestParticipationController::class, 'accept'])
        ->name('battles.participation.accept');
    Route::post('/battles/{id}/decline', [\App\Http\Controllers\ContestParticipationController::class, 'decline'])
        ->name('battles.participation.decline');
    Route::post('/contests/{type}/{id}/reports', [\App\Http\Controllers\ContestReportingController::class, 'store'])
        ->where('type', 'challenge|battle')
        ->name('contests.reports.store');
});
// Dashboard Route

Route::group(['middleware' => 'auth'], function (): void {
    //
});
Route::group(['prefix' => 'stories'], function (): void {
    Route::get('/', ['as' => 'stories.catalog', 'uses' => 'Api\ApiController@get_stories']);
    Route::post('repost', ['as' => 'stories.repost', 'uses' => 'Api\StoryController@repost']);
});

Route::group(['prefix' => 'challenges'], function (): void {
    Route::get('/', ['as' => 'challenges.catalog', 'uses' => 'Api\ChallengeController@get_challenges']);
    Route::get('/show/{id}', ['as' => 'challenge_page', 'uses' => 'Api\ChallengeController@show']);
});
Route::group(['prefix' => 'battles'], function (): void {
    Route::get('/show/{id}', ['as' => 'battle_page', 'uses' => 'Api\BattleController@show']);
});


Route::group(['prefix' => 'dashboard', 'middleware' => 'auth'], function (): void {
    Route::get('/', ['as' => 'dashboard', 'uses' => 'DashboardController@dashboard']);

    Route::get('thanks', [PaymentCommentController::class, 'thankList'])->name('thank_list');
    Route::get('abuses_list', [\App\Http\Controllers\AbuseController::class, 'abuses_list'])->name('abuses_list');
    Route::post('abuses_list', [\App\Http\Controllers\AbuseController::class, 'abuses_list_action'])->name('abuses_list_action');

    Route::get('autopayments', 'PaymentController@autopayments')->name('autopayments');
    Route::get('autopayments/{id}/delete', 'PaymentController@deleteAutopayment')->name('autopayments_delete');

    Route::get('action_campaigns', [ActionsController::class, 'getMyActions']);

    Route::group(['prefix' => 'stories'], function (): void {
        Route::get('create', ['as' => 'stories.create', 'uses' => 'Api\StoryController@create']);
        Route::post('remove', ['as' => 'stories.remove', 'uses' => 'Api\StoryController@remove']);
        Route::post('exclude-useful', ['as' => 'stories.exclude_useful', 'uses' => 'Api\StoryController@excludeUseful']);
    });

    Route::group(['prefix' => 'challenges'], function (): void {
        Route::get('/', ['as' => 'user_challenges', 'uses' => 'Dashboard\ChallengeDashboardController@list']);
        Route::get('/show/{id}', ['as' => 'dashboard_challenge_page', 'uses' => 'Dashboard\ChallengeDashboardController@show']);
        Route::get('/edit/{id}', ['as' => 'challenges.edit', 'uses' => 'Dashboard\ChallengeDashboardController@edit']);
        Route::get('create', ['as' => 'challenges.create', 'uses' => 'Dashboard\ChallengeDashboardController@create']);
        Route::get('invites/users', ['as' => 'challenges.invites.users', 'uses' => 'Dashboard\ChallengeDashboardController@inviteUsers']);
        Route::get('stop/{id}', ['as' => 'challenges.stop', 'uses' => 'Dashboard\ChallengeDashboardController@stop']);
        Route::post('remove', ['as' => 'challenges.remove', 'uses' => 'Dashboard\ChallengeDashboardController@remove']);
    });

    Route::get('battles/show/{id}', ['as' => 'dashboard_battle_page', 'uses' => 'Dashboard\DashboardBattlesController@show']);
    Route::get('battles/edit/{id}', ['as' => 'battles.edit', 'uses' => 'Dashboard\DashboardBattlesController@edit']);

    Route::group(['prefix' => 'battles', 'middleware' => 'admin'], function (): void {
        Route::get('/', ['as' => 'user_battles', 'uses' => 'Dashboard\DashboardBattlesController@list']);
        Route::get('create', ['as' => 'battles.create', 'uses' => 'Dashboard\DashboardBattlesController@create']);
        Route::get('stop/{id}', ['as' => 'battles.stop', 'uses' => 'Dashboard\DashboardBattlesController@stop']);
        Route::post('remove', ['as' => 'battles.remove', 'uses' => 'Dashboard\DashboardBattlesController@remove']);
    });


    Route::group(['prefix' => 'my_campaigns'], function (): void {
        Route::get('/', ['as' => 'my_campaigns', 'uses' => 'CampaignsController@myCampaigns']);
        Route::get('my_pending_campaigns', ['as' => 'my_pending_campaigns', 'uses' => 'CampaignsController@myPendingCampaigns']);

        Route::get('start_campaign', ['as' => 'start_campaign', 'uses' => 'CampaignsController@create']);
        Route::post('start_campaign', ['uses' => 'CampaignsController@store']);

        Route::get('edit_campaign/{id}', ['as' => 'edit_campaign', 'uses' => 'CampaignsController@edit']);
        Route::get('delete_campaign/{id}', ['as' => 'delete_campaign', 'uses' => 'CampaignsController@delete_campaign']);
        Route::post('edit_campaign/{id}', ['uses' => 'CampaignsController@update']);
        Route::post('wake_campaign/{id}', ['as' => 'wake_campaign', 'uses' => 'CampaignsController@wakeUp']);

        // Reward
        Route::get(
            'edit_campaign/{id}/rewards',
            ['as' => 'edit_campaign_rewards', 'uses' => 'CampaignsController@rewardsInCampaignEdit']
        );
        Route::post('edit_campaign/{id}/rewards', ['uses' => 'RewardController@store']);

        Route::get('edit_campaign/{id}/rewards/update/{reward_id}', ['as' => 'reward_update', 'uses' => 'RewardController@edit']);
        Route::post('edit_campaign/{id}/rewards/update/{reward_id}', ['uses' => 'RewardController@update']);
        Route::post('delete_reward', ['as' => 'delete_reward', 'uses' => 'RewardController@destroy']);

        // Updates
        Route::get('edit_campaign/{id}/updates', ['as' => 'edit_campaign_updates', 'uses' => 'UpdateController@index']);
        Route::post('edit_campaign/{id}/updates', ['uses' => 'UpdateController@store']);

        Route::get('edit_campaign/{id}/updates/update/{update_id}', ['as' => 'update_update', 'uses' => 'UpdateController@edit']);
        Route::post('edit_campaign/{id}/updates/update/{update_id}', ['uses' => 'UpdateController@update']);
        Route::post('delete_update', ['as' => 'delete_update', 'uses' => 'UpdateController@destroy']);

        // Faq

        Route::get('edit_campaign/{id}/faqs', ['as' => 'edit_campaign_faqs', 'uses' => 'FaqController@index']);
        Route::post('edit_campaign/{id}/faqs', ['uses' => 'FaqController@store']);
        Route::get('edit_campaign/{id}/faqs/update/{faq_id}', ['as' => 'faq_update', 'uses' => 'FaqController@edit']);
        Route::post('edit_campaign/{id}/faqs/update/{faq_id}', ['uses' => 'FaqController@update']);
        Route::post('delete_faq', ['as' => 'delete_faq', 'uses' => 'FaqController@destroy']);
        // Route::get('my_campaigns', ['as'=>'my_campaigns', 'uses' => 'CampaignsController@myCampaigns']);
    });

    Route::get('wallet', ['as' => 'user_wallet', 'uses' => 'WalletController@index']);
    Route::get('stories', ['as' => 'user_stories', 'uses' => 'Api\StoryController@user_stories']);
    Route::post('wallet_deposit', ['as' => 'wallet_deposit', 'uses' => 'WalletController@wallet_deposit']);
    Route::post('wallet_withdraw', ['as' => 'wallet_withdraw', 'uses' => 'WalletController@wallet_withdraw']);

    Route::group(['prefix' => 'admin_comments'], function (): void {
        Route::get('/', ['as' => 'admin_comments', 'uses' => 'CommentController@index']);
        Route::post('action', ['as' => 'comment_action', 'uses' => 'CommentController@commentAction']);
    });

    Route::group(['prefix' => 'admin_stories'], function (): void {
        Route::get('/', ['as' => 'admin_stories', 'uses' => 'Api\StoryController@stories_list']);
        Route::get('likes', ['as' => 'admin_stories_likes', 'uses' => 'Api\StoryController@admin_stories_likes']);
        Route::get('dislikes', ['as' => 'admin_stories_dislikes', 'uses' => 'Api\StoryController@admin_stories_dislikes']);
        Route::post('action', ['as' => 'admin_stories.confirm', 'uses' => 'Api\StoryController@confirm']);
        Route::post('add_likes', ['as' => 'admin_stories.add_likes', 'uses' => 'Api\StoryController@add_likes']);
    });

    Route::group(['prefix' => 'admin_games'], function (): void {
        Route::get('/', ['as' => 'admin_games', 'uses' => 'Games\GameController@index']);
        Route::post('/', ['as' => 'admin_games', 'uses' => 'Games\GameController@update']);
    });

    Route::group(['prefix' => 'admin_game_sessions'], function (): void {
        Route::get('/', ['as' => 'admin_game_sessions', 'uses' => 'Games\GameSessionController@list']);
    });



    Route::group(['prefix' => 'admin_challenges_stories'], function (): void {
        Route::get('/', ['as' => 'admin_challenges_stories', 'uses' => 'Api\StoryController@challenges_stories_list']);
        Route::post('action', ['as' => 'admin_challenge_stories.confirm', 'uses' => 'Api\StoryController@admin_challenge_stories_confirm']);
    });

    Route::group(['prefix' => 'admin_stories_ads'], function (): void {
        Route::get('/', ['as' => 'admin_stories_ads', 'uses' => 'Api\AdsController@ads_list']);
    });


    Route::group(['prefix' => 'admin_battles_stories'], function (): void {
        Route::get('/', ['as' => 'admin_battles_stories', 'uses' => 'Api\BattleController@battles_stories_list']);
        Route::post('action', ['as' => 'admin_battles_stories.confirm', 'uses' => 'Api\BattleController@admin_battle_stories_confirm']);
    });


    Route::group(['prefix' => 'admin_challenges'], function (): void {
        Route::get('/', ['as' => 'admin_challenges', 'uses' => 'Api\ChallengeController@challenges_list']);
        Route::post('action', ['as' => 'admin_challenges.confirm', 'uses' => 'Api\ChallengeController@confirm']);
    });

    Route::group(['prefix' => 'admin_battles'], function (): void {
        Route::get('/', ['as' => 'admin_battles', 'uses' => 'Dashboard\DashboardBattlesController@index']);
        Route::post('action', ['as' => 'admin_battles.confirm', 'uses' => 'Api\BattleController@confirm']);
    });


    Route::group(['prefix' => 'admin_tags'], function (): void {
        Route::get('/', ['as' => 'admin_tags', 'uses' => 'TagController@index']);
    });

    Route::group(['prefix' => 'user_likes'], function (): void {
        Route::get('/', ['as' => 'user_likes', 'uses' => 'Api\StoryController@likes_list']);
    });

    Route::group(['prefix' => 'user_followers'], function (): void {
        Route::get('/', ['as' => 'user_followers', 'uses' => 'FollowController@follow_list']);
    });

    Route::group(['prefix' => 'user_friends'], function (): void {
        Route::get('/', ['as' => 'user_friends', 'uses' => 'FollowController@friends_list']);
    });

    Route::group(['prefix' => 'user_followings'], function (): void {
        Route::get('/', ['as' => 'user_followings', 'uses' => 'FollowController@following_list']);
    });


    //messenger
    Route::group(['prefix' => 'messages'], function () {
        Route::get('/', ['as' => 'messages', 'uses' => 'MessagesController@index']);
        Route::get('send/{id}', ['as' => 'messages.create', 'uses' => 'MessagesController@create']);
        Route::post('/', ['as' => 'messages.store', 'uses' => 'MessagesController@store'])->middleware('suspicious.restricted');
        Route::get('show', ['as' => 'messages.show', 'uses' => 'MessagesController@show']);
        Route::post('send_message', ['as' => 'messages.send_message', 'uses' => 'MessagesController@send_message'])->middleware(['suspicious.restricted', 'action.limit.message']);
        Route::get('get_list', ['as' => 'messages.get_list', 'uses' => 'MessagesController@get_list']);
    });

    /**
     * Restricted area only for admin with middleware->admin.
     */
    Route::group(['middleware' => 'admin'], function (): void {
        Route::group(['prefix' => 'categories'], function (): void {
            Route::get('/', ['as' => 'categories', 'uses' => 'CategoriesController@index']);
            Route::post('/', ['uses' => 'CategoriesController@store']);
            Route::get('edit/{id}', ['as' => 'edit_categories', 'uses' => 'CategoriesController@edit']);
            Route::post('edit/{id}', ['uses' => 'CategoriesController@update']);
            Route::post('delete-categories', ['as' => 'delete_categories', 'uses' => 'CategoriesController@destroy']);
        });

        Route::group(['prefix' => 'campaigns'], function (): void {
            Route::get('all_campaigns', ['as' => 'all_campaigns', 'uses' => 'CampaignsController@allCampaigns']);
            Route::get('staff_picks', ['as' => 'staff_picks', 'uses' => 'CampaignsController@staffPicksCampaigns']);
            Route::get('funded', ['as' => 'funded', 'uses' => 'CampaignsController@fundedCampaigns']);
            Route::get('blocked_campaigns', ['as' => 'blocked_campaigns', 'uses' => 'CampaignsController@blockedCampaigns']);
            Route::get('pending_campaigns', ['as' => 'pending_campaigns', 'uses' => 'CampaignsController@pendingCampaigns']);

            Route::get('expired_campaigns', ['as' => 'expired_campaigns', 'uses' => 'CampaignsController@expiredCampaigns']);
            Route::get(
                'campaign-search',
                ['as' => 'campaign_admin_search', 'uses' => 'CampaignsController@searchAdminCampaigns']
            );

            Route::get('moderate', ['as' => 'campaigns_to_moderate', 'uses' => 'CampaignsController@campaignsToModerate']);
            Route::get(
                'campaign_status/{id}/{status}',
                ['as' => 'campaign_status', 'uses' => 'CampaignsController@statusChange']
            );

            Route::get('campaign_delete/{id}', ['as' => 'campaign_delete', 'uses' => 'CampaignsController@deleteCampaigns']);

            Route::post('{campaign}/sliderOrder', ['as' => 'slider_order', 'uses' => 'CampaignsController@sliderOrder']);
        });

        // Settings
        Route::group(['prefix' => 'settings'], function (): void {
            Route::get('theme-settings', ['as' => 'theme_settings', 'uses' => 'SettingsController@ThemeSettings']);
            Route::get('general', ['as' => 'general_settings', 'uses' => 'SettingsController@GeneralSettings']);
            Route::get('payments', ['as' => 'payment_settings', 'uses' => 'SettingsController@PaymentSettings']);

            Route::get('social', ['as' => 'social_settings', 'uses' => 'SettingsController@SocialSettings']);
            Route::get('recaptcha', ['as' => 're_captcha_settings', 'uses' => 'SettingsController@reCaptchaSettings']);

            // Save settings / options
            Route::post('save-settings', ['as' => 'save_settings', 'uses' => 'SettingsController@update']);
        });

        Route::group(['prefix' => 'pages'], function (): void {
            Route::get('/', ['as' => 'pages', 'uses' => 'PostController@index']);

            Route::get('create', ['as' => 'create_new_page', 'uses' => 'PostController@create']);
            Route::post('create', ['uses' => 'PostController@store']);
            Route::post('delete', ['as' => 'delete_page', 'uses' => 'PostController@destroy']);

            Route::get('edit/{slug}', ['as' => 'edit_page', 'uses' => 'PostController@edit']);
            Route::post('edit/{slug}', ['uses' => 'PostController@updatePage']);
        });

        Route::group(['prefix' => 'news'], function (): void {
            Route::get('', ['as' => 'news_list', 'uses' => 'NewsController@index']);
            Route::get('create', ['as' => 'news_create_page', 'uses' => 'NewsController@create']);
            Route::post('create', ['as' => 'news_create', 'uses' => 'NewsController@store']);
            Route::get('edit/{id}', ['as' => 'new_edit_page', 'uses' => 'NewsController@edit']);
            Route::post('edit/{id}', ['uses' => 'NewsController@update']);
        });

        Route::group(['prefix' => 'users'], function (): void {
            Route::get('/', ['as' => 'users', 'uses' => 'UserController@index']);
            Route::get('view/{slug}', ['as' => 'users_view', 'uses' => 'UserController@show']);
            Route::get('user_status/{id}/{status}', ['as' => 'user_status', 'uses' => 'UserController@statusChange']);
            Route::post('notify/{id}', ['as' => 'users_notify', 'uses' => 'UserController@sendChatNotify']);
            Route::post('suspicious/{id}', ['as' => 'users_suspicious_moderation', 'uses' => 'UserController@suspiciousModeration']);

            // Edit
            Route::get('edit/{id}', ['as' => 'users_edit', 'uses' => 'UserController@profileEdit']);
            //Route::post('edit/{id}', ['uses' => 'UserController@profileEditPost']);

            Route::get('delete/{id}', ['as' => 'user_delete', 'uses' => 'UserController@userDeleteByAdmin']);
            Route::get('user_auth/{id}', ['as' => 'user_auth', 'uses' => 'UserController@userAuthByAdmin']);

            Route::get(
                'requested-account-deletion',
                ['as' => 'requested_account_deletion', 'uses' => 'UserController@requestedAccountDeletion']
            );
            Route::post(
                'view/{slug}/requested-deletion-action',
                ['as' => 'requested_deletion_action', 'uses' => 'UserController@requestedDeletionAction']
            );
        });

        Route::group(['prefix' => 'withdrawal-requests'], function (): void {
            Route::get('/', ['as' => 'withdrawal_requests', 'uses' => 'PaymentController@withdrawalRequests']);
            Route::post('/', ['as' => 'withdrawal_requests', 'uses' => 'WalletController@withdrawalRequestsConfirmation']);
        });
        Route::group(['prefix' => 'logs'], function (): void {
            Route::get('/', ['as' => 'logs', 'uses' => 'LogsController@index']);
        });

        Route::get('/mailing', ['as' => 'mailing', 'uses' => 'NewsController@mailing']);
        Route::get('/mailing_mails/{id}', ['as' => 'mailing_mails', 'uses' => 'NewsController@mailing_mails']);
        Route::get('/mailing_mails_show/{id}', ['as' => 'mailing_mails_show', 'uses' => 'NewsController@mailing_mails_show']);
        Route::post('/mailing_mails/{id}', ['as' => 'mailing_mails', 'uses' => 'NewsController@mailing_mails']);
        Route::get('/send_single_mail/{id}', ['as' => 'send_single_mail', 'uses' => 'NewsController@send_single_mail']);
        Route::get('/remove_single_mail/{id}', ['as' => 'remove_single_mail', 'uses' => 'NewsController@remove_single_mail']);
        Route::post('/mailing', ['as' => 'mailing_save', 'uses' => 'NewsController@mailingSave']);
    });

    Route::group(['prefix' => 'payments'], function (): void {
        Route::get('/', ['as' => 'payments', 'uses' => 'PaymentController@index']);
        Route::get('pending', ['as' => 'payments_pending', 'uses' => 'PaymentController@paymentsPending']);
        Route::get('view/{id}', ['as' => 'payment_view', 'uses' => 'PaymentController@view']);
        Route::get('status-change/{id}/{status}', ['as' => 'status_change', 'uses' => 'PaymentController@markSuccess']);
        Route::get('success', ['as' => 'success.payment', 'uses' => 'PaymentController@successPage']);
    });

    Route::group(['prefix' => 'transactions'], function (): void {
        Route::get('/', ['as' => 'transactions', 'uses' => 'TransactionController@index']);
        Route::post('/', ['as' => 'transactions_deposit', 'uses' => 'TransactionController@transactions_deposit']);
    });

    Route::group(['prefix' => 'stats'], function (): void {
        Route::get('/', ['as' => 'stats', 'uses' => 'StatsController@index']);
    });


    Route::group(['prefix' => 'withdraw'], function (): void {
        Route::get('/', ['as' => 'withdraw', 'uses' => 'PaymentController@withdraw']);
        Route::post('/', ['as' => 'withdraw_request', 'uses' => 'PaymentController@withdrawRequest']);
        Route::post('/wallet', ['as' => 'withdraw_wallet_request', 'uses' => 'WalletController@withdrawWalletRequest']);

        Route::get('view/{id}', ['as' => 'withdraw_request_view', 'uses' => 'PaymentController@withdrawRequestView']);
        Route::post('view/{id}', ['uses' => 'PaymentController@withdrawalRequestsStatusSwitch']);
    });

    Route::get('backed_campaigns', ['as' => 'backed_campaigns', 'uses' => 'PaymentController@backedCampaigns']);

    Route::get('profile/{id}', ['uses' => 'UserController@user_profile'])->name('user.profile');
    Route::controller(UserController::class)->prefix('u')->group(function (): void {
        Route::get('profile', 'profile')->name('profile');
        Route::get('profile/edit', 'profileEdit')->name('profile_edit');
        Route::post('profile/delete', 'delete')->name('profile.delete');
        Route::get('profile/delete_confirm/{token}', 'delete_confirm')->name('profile.delete_confirm');
        Route::get('profile/settings', 'profileSettings')->name('profile_settings');
        Route::post('profile/update', 'update')->name('profile_settings_save');
        Route::post('profile/change-avatar', 'changeAvatar')->name('change_avatar');
        //Route::post('upload-avatar', 'uploadAvatar')->name('upload_avatar');

        Route::get('withdrawal-preference', 'withdrawalPreference')->name('withdrawal_preference');
        Route::post('withdrawal-preference', 'withdrawalPreferenceUpdate');

        /** Change Password routes. */
        Route::prefix('account')->group(function (): void {
            Route::get('change-password', ['as' => 'change_password', 'uses' => 'UserController@changePassword']);
            Route::post('change-password', 'UserController@changePasswordPost');

            Route::get('delete', ['as' => 'request_account_deletion', 'uses' => 'UserController@accountDeletion']);
            Route::post('delete', 'UserController@accountDeletionPost');
        });

        /** Rewards Digital Content Downloads.*/
        Route::get(
            'rewards-digital-downloads/{reward_id}',
            ['as' => 'rewards_digital_downloads', 'uses' => 'CampaignsController@rewardDigitalDownloads']
        );
    });

    Route::group(['prefix' => 'media'], function (): void {
        Route::post('upload', ['as' => 'post_media_upload', 'uses' => 'MediaController@store']);
        Route::get('load_filemanager', ['as' => 'load_filemanager', 'uses' => 'MediaController@loadFileManager']);
        Route::post('delete', ['as' => 'delete_media', 'uses' => 'MediaController@delete']);
    });
});

Route::any('/yandex_kassa/confirm', ['as' => 'kassa_confirm', 'uses' => 'CampaignsController@confirmPayment']);
Route::get('/payment_success', ['as' => 'kassa_success', 'uses' => 'CampaignsController@successPayment']);
Route::get('/success_payment', ['as' => 'tinkoff_success', 'uses' => 'PaymentController@successPage']);

Route::get('/action_campaigns', [ActionsController::class, 'campaignsHtmlByCategory']);
Route::get('/action_campaigns/{campaign}', [ActionsController::class, 'getCampaign']);

Route::get('/payment/success', function (Request $request) {
    if ($cookie = request()?->cookie('payed_campaign')) {
        return redirect(route('campaign_single', $cookie) . '?success_pay=1');
    }

    return redirect(route('tinkoff_success'));
});

Route::get('/mail_track', function (Request $request) {
    $mail = Mailing::find($request->get('mail_id'));
    if ($request->query->count() === 1) {
        $mail->increment('opened');
    }
    if ($request->get('action') === 'click') {
        $mail->increment('clicked', 1);

        return redirect($request->get('redirect'));
    }
    return file_get_contents(public_path('/dist/pixel.png'));
});

//Route::get('/removeFromMailList', function (Request $request) {
//    $user = \App\Models\User::where('email', $request->get('email'))->firstOrFail();
//
//    //    $user->update(['unsubscribe' => true]);
//
//    echo 'Вы отписаны от рассылки';
//});

Route::get('removeFromMailList', ['as' => 'removeFromMailList', 'uses' => '\App\Http\Controllers\UserController@removeFromMailList']);


// Lastly we serving single page without any URL Segment
Route::get('{slug}', ['as' => 'single_page', 'uses' => 'PostController@showPage']);
Route::get('docs/swagger', ['as' => 'swagger', 'uses' => '\App\Http\Controllers\Api\ApiController@swagger']);


Route::resource('videos', \App\Http\Controllers\Api\MediaController::class);

//Route::group(['prefix' => 'api'], function() {
//    Route::get('/me', function() {
//        return auth()->user();
//    });
//    Route::get('/users/{id}', function($id) {
//        return \App\Models\User::find($id);
//    });
//    Route::get('/campaigns/stories', function($id) {
//        return \App\Models\Media::query()
//                                ->where('mime_type', 'like', 'video%')
//                                ->inRandomOrder()
//                                ->paginate(5);
//    });
//    Route::get('/me', function() {
//        dump(auth()->user());
//        return response()->json(auth()->user());
//    });
//    Route::get('/users/{id}', function($id) {
//        return response()->json(\App\Models\User::find($id));
//    });
//});
