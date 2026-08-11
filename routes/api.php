<?php

declare(strict_types=1);

use App\Http\Controllers\ActionsController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CampaignsController;
use App\Http\Controllers\PaymentCommentController;
use App\Http\Controllers\UserController;
use App\Models\Mailing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/*
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});*/

Route::post('/auth_token', 'Api\ApiController@create_token');
Route::get('/vk_auth/link', 'Api\AuthController@vk_auth_link');
Route::post('/vk_auth', 'Api\AuthController@vk_authenticate');
Route::post('/apple_auth', 'Api\AuthController@apple_authenticate');
Route::post('/yandex_auth', 'Api\AuthController@yandex_authenticate');


Route::post('/user/sendEmailCode', 'UserController@sendCode');
Route::post('/user/sendPhoneCode', 'UserController@activation_resend');
Route::post('/user/phone_update', 'UserController@phone_update');
Route::post('/register', 'Auth\RegisterController@api_register');
Route::post('/password/reset', 'Api\ApiController@sendResetLinkEmail');
Route::post('/test_upload', 'Api\ApiController@test_upload');

Route::get('/games', 'Api\Games\GameController@list');
Route::get('/games/{id?}', 'Api\Games\GameSessionController@list');
Route::get('/game_session/{id}', 'Api\Games\GameSessionController@get');
Route::post('/game_session/create', 'Api\Games\GameSessionController@create');
Route::post('/game_session/{id}', 'Api\Games\GameSessionController@update');


Route::post('/user/activation_resend', 'UserController@activation_resend')->name('activation_resend');
Route::post('/user/activation_verify', 'UserController@activation_verify')->name('activation_verify_api');

Route::middleware(['auth:sanctum', 'update.user.data'])->group(function () {
    Route::post('/user/email', 'UserController@saveEmailAndSendCode')->middleware('throttle:5,1');
    Route::post('/user/email/verify', 'UserController@verifyEmailCode')->middleware('throttle:10,1');
    Route::post('/user/email-prompt/postpone', 'UserController@postponeEmailPrompt')->middleware('throttle:10,1');
    Route::post('/user/phone', 'UserController@savePhoneAndSendCode')->middleware('throttle:5,1');
    Route::post('/user/phone/resend', 'UserController@resendPhoneCode')->middleware('throttle:5,1');
    Route::post('/user/phone/verify', 'UserController@verifyPhoneCode')->middleware('throttle:10,1');
    Route::post('/user/phone-prompt/postpone', 'UserController@postponePhonePrompt')->middleware('throttle:10,1');
    Route::post('/firebase_token', [\App\Http\Controllers\WebNotificationController::class, 'storeToken'])->name('firebase.token');
    Route::get('/user/{id?}', 'Api\ApiController@account_info');
    Route::post('/user/events/{event}/dismiss', 'Api\ApiController@dismissEvent');
    Route::post('/user/abuse', ['as' => 'api.user.abuse', 'uses' => 'AbuseController@abuse'])->middleware('suspicious.restricted');
    Route::get('/user/abuse', ['as' => 'api.user.abuse', 'uses' => 'AbuseController@get_abuse']);
    Route::post('/user/follow', ['as' => 'api.follow_toggle', 'uses' => 'FollowController@follow_toggle'])->middleware('suspicious.restricted');
    Route::get('/user_friends', ['as' => 'api.user_friends', 'uses' => 'FollowController@friends_list']);
    Route::get('/user_followers', ['as' => 'api.user_followers', 'uses' => 'FollowController@follow_list']);
    Route::get('/user_followings', ['as' => 'api.user_followings', 'uses' => 'FollowController@following_list']);
    Route::get('/contacts/prompt-state', 'Api\ContactsController@promptState');
    Route::post('/contacts/deny', 'Api\ContactsController@deny');
    Route::post('/contacts/import', 'Api\ContactsController@import');
    Route::get('/contacts/suggestions', 'Api\ContactsController@suggestions');
    Route::post('/contacts/suggestions/follow', 'Api\ContactsController@followSuggestions')->middleware('suspicious.restricted');
    Route::post('/vk/friends/import', 'Api\ContactsController@importVkFriends');
    Route::post('campaigns/like', ['as' => 'api.addLike', 'uses' => 'CampaignsController@addLike'])->middleware('suspicious.restricted');
    Route::post('campaigns/donate', ['as' => 'campaigns.donate', 'uses' => 'Api\ApiController@getCampaignPaymentUrl']);
    Route::post('campaigns/wallet_donate', ['as' => 'campaigns.wallet.donate', 'uses' => 'CampaignsController@campaign_donate']);
    // Comments
    Route::post('campaigns/comment', ['as' => 'api.post_comments', 'uses' => 'CommentController@postCommentsApi'])->middleware('suspicious.restricted');
    Route::get('profile/likes', ['as' => 'profile.likes_list', 'uses' => 'Api\StoryController@likes_list']);

    Route::controller(UserController::class)->prefix('')->group(function (): void {
        Route::get('profile', 'profile');
        Route::post('profile/delete', 'delete_api');
//        Route::get('profile/edit', 'profileEdit');
//        Route::get('profile/settings', 'profileSettings');
        Route::post('profile/update', 'api_update');
        Route::post('profile/change-avatar', 'changeAvatar');
        //Route::post('upload-avatar', 'uploadAvatar');

        Route::get('withdrawal-preference', 'withdrawalPreference');
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

    Route::group(['prefix' => 'wallet'], function (): void {
        Route::get('transactions', ['as' => 'wallet.api.transactions', 'uses' => 'Api\WalletController@transactions']);
        Route::post('deposit', ['as' => 'wallet.api.deposit', 'uses' => 'Api\WalletController@wallet_deposit']);
        Route::post('withdraw', ['as' => 'wallet.api.withdraw', 'uses' => 'WalletController@withdrawWalletRequest']);
        Route::any('appstore_validation', 'Api\WalletController@app_store');
    });

     Route::group(['prefix' => 'stories'], function (): void {
         Route::post('/custom_donate', ['as' => 'stories.api.pay', 'uses' => 'Api\StoryController@pay']);
    });

    Route::group(['prefix' => 'messages'], function () {
        Route::get('get_list', ['as' => 'api.messages.get_list', 'uses' => 'Api\MessagesController@get_list']);
        Route::get('show', ['as' => 'api.messages.show', 'uses' => 'Api\MessagesController@show']);

        Route::get('send/{id}', ['as' => 'api.messages.create', 'uses' => 'Api\MessagesController@create']);
        Route::post('/', ['as' => 'api.messages.store', 'uses' => 'Api\MessagesController@store'])->middleware('suspicious.restricted');

        Route::post('send_message', ['as' => 'api.messages.send_message', 'uses' => 'Api\MessagesController@send_message'])->middleware(['suspicious.restricted', 'action.limit.message']);
        Route::post('mark_as_read', ['as' => 'api.messages.mark_as_read', 'uses' => 'Api\MessagesController@mark_as_read']);
        Route::post('delete_thread', ['as' => 'api.messages.delete', 'uses' => 'Api\MessagesController@delete_thread']);

    });


});

Route::get('campaigns/{id}/comments', ['as' => 'api.post_comments_list', 'uses' => 'CommentController@comments_list']);

Route::post('/services/stream_donate', ['as' => 'stream_donate', 'uses' => 'Api\ApiController@stream_donate']);
Route::post('/services/stream_status', ['as' => 'stream.status', 'uses' => 'Api\ApiController@stream_status']);
Route::get('/services/coins_bank', ['as' => 'coins_bank', 'uses' => 'Api\ApiController@coins_bank']);
Route::any('/services/smsc/callback', ['as' => 'smsc.validate', 'uses' => 'Api\SMSCController@callback']);
Route::any('/services/notifications', ['as' => 'firebase.notify.all', 'uses' => 'WebNotificationController@sendWebNotification']);
Route::any('/services/push', ['as' => 'firebase.send.push', 'uses' => 'Api\ApiController@sendPush']);

Route::group(['prefix' => 'messages', ], function (): void {
    Route::post('/store-token', [\App\Http\Controllers\WebNotificationController::class, 'storeToken'])->name('store.token');
});

Route::group(['prefix' => 'stories', 'middleware' => 'update.user.data'], function (): void {

    Route::group(['middleware' => 'web'], function (): void {
        Route::post('store_web', ['as' => 'stories.store.web', 'uses' => 'Api\StoryUploadController@store_web']);
    });

    Route::get('/get/{id}', ['as' => 'stories.get', 'uses' => 'Api\StoryController@get']);
    Route::get('/preview/{id}', ['as' => 'stories.preview', 'uses' => 'Api\StoryController@getPreview']);
    Route::post('/donate/{id}', ['as' => 'stories.donate', 'uses' => 'Api\StoryController@donate']);
    Route::post('/pay/{id}', ['as' => 'stories.pay', 'uses' => 'Api\StoryController@pay']);

    Route::get('/get_video/{id}', ['as' => 'stories.get.video', 'uses' => 'Api\StoryController@get_file']);

});

Route::group(['prefix' => 'stories', 'middleware' => ['auth:sanctum', 'update.user.data']], function (): void {
    Route::post('store', ['as' => 'stories.store', 'uses' => 'Api\StoryUploadController@store']);
    Route::post('upload', ['as' => 'stories.upload', 'uses' => 'Api\StoryUploadController@videoUpload']);
    Route::post('delete', ['as' => 'stories.remove', 'uses' => 'Api\StoryController@remove']);
    Route::post('/like', ['as' => 'stories.like', 'uses' => 'Api\StoryController@like'])->middleware(['suspicious.restricted', 'action.limit.like']);
    Route::post('/dislike', ['as' => 'stories.dislike', 'uses' => 'Api\StoryController@dislike'])->middleware('suspicious.restricted');
    Route::post('/comment', ['as' => 'stories.comment', 'uses' => 'Api\StoryController@comment'])->middleware('suspicious.restricted');
    Route::post('/comment/like', ['as' => 'stories.comment.like', 'uses' => 'Api\StoryController@commentLike'])->middleware('suspicious.restricted');
});
Route::post('/stories/comment', ['as' => 'stories.comment.web', 'uses' => 'Api\StoryController@comment'])->middleware('suspicious.restricted');
Route::post('/stories/like', ['as' => 'stories.like.web', 'uses' => 'Api\StoryController@like'])->middleware(['suspicious.restricted', 'action.limit.like']);
Route::post('/stories/dislike', ['as' => 'stories.dislike.web', 'uses' => 'Api\StoryController@dislike'])->middleware('suspicious.restricted');
Route::post('/stories/comment/like', ['as' => 'stories.comment.like.web', 'uses' => 'Api\StoryController@commentLike'])->middleware('suspicious.restricted');

Route::group(['prefix' => 'challenges'], function (): void {
    Route::get('/', ['as' => 'challenges.list', 'uses' => 'Api\ChallengeController@get_challenges']);
    Route::get('popular_answers', ['as' => 'challenges.popular_answers', 'uses' => 'Api\ChallengeController@get_popular_answers']);
    Route::group(['middleware' => 'web'], function (): void {
        Route::post('store_web', ['as' => 'challenges.store.web', 'uses' => 'Api\ChallengeController@store_web']);
        Route::post('update_web', ['as' => 'challenges.update.web', 'uses' => 'Api\ChallengeController@store_web']);
    });
    Route::get('/get/{id}', ['as' => 'challenges.get', 'uses' => 'Api\ChallengeController@get']);
    Route::get('/preview/{id}', ['as' => 'challenges.preview', 'uses' => 'Api\ChallengeController@getPreview']);
    Route::get('/get_video/{id}', ['as' => 'challenges.get.video', 'uses' => 'Api\ChallengeController@get_file']);
    Route::group(['middleware' => ['auth:sanctum', 'update.user.data']], function (): void {
        Route::post('store', ['as' => 'challenges.store', 'uses' => 'Api\ChallengeController@store_web']);
        Route::post('delete', ['as' => 'challenges.remove', 'uses' => 'Api\ChallengeController@remove']);
        Route::post('select_winners', ['as' => 'challenges.select_winners', 'uses' => 'Api\ChallengeController@selectWinners']);
    });
});

Route::group(['prefix' => 'battles'], function (): void {
    Route::get('/', ['as' => 'battles.list', 'uses' => 'Api\BattleController@get_battles']);
    Route::get('popular_answers', ['as' => 'battles.popular_answers', 'uses' => 'Api\BattleController@get_popular_answers']);
    Route::get('stories_list', ['as' => 'battles.stories', 'uses' => 'Api\BattleController@get_stories']);
    Route::group(['middleware' => 'web'], function (): void {
        Route::post('store_web', ['as' => 'battles.store.web', 'uses' => 'Api\BattleController@store_web']);
        Route::post('update_web', ['as' => 'battles.update.web', 'uses' => 'Api\BattleController@store_web']);
    });
    Route::get('/get/{id}', ['as' => 'battles.get', 'uses' => 'Api\BattleController@get']);
    Route::get('/preview/{id}', ['as' => 'battles.preview', 'uses' => 'Api\BattleController@getPreview']);
    Route::get('/get_video/{id}', ['as' => 'battles.get.video', 'uses' => 'Api\BattleController@get_file']);
    Route::group(['middleware' => ['auth:sanctum', 'update.user.data']], function (): void {
        Route::post('store', ['as' => 'battles.store', 'uses' => 'Api\BattleController@store_web']);
        Route::post('delete', ['as' => 'battles.remove', 'uses' => 'Api\BattleController@remove']);
    });
});

Route::group(['middleware' => ['auth:sanctum', 'update.user.data']], function (): void {
    Route::get('/contests/invites/users', [\App\Http\Controllers\Dashboard\ChallengeDashboardController::class, 'inviteUsers']);
    Route::get('/contests/{type}/{id}/invites/users', [\App\Http\Controllers\ContestInvitationController::class, 'users'])
        ->where('type', 'challenge|battle');
    Route::post('/contests/{type}/{id}/invites', [\App\Http\Controllers\ContestInvitationController::class, 'store'])
        ->where('type', 'challenge|battle');
    Route::post('/contests/{type}/{id}/join', [\App\Http\Controllers\ContestParticipationController::class, 'join'])
        ->where('type', 'challenge|battle');
    Route::post('/contests/{type}/{id}/leave', [\App\Http\Controllers\ContestParticipationController::class, 'leave'])
        ->where('type', 'challenge|battle');
    Route::post('/contests/{type}/{id}/rejoin', [\App\Http\Controllers\ContestParticipationController::class, 'rejoin'])
        ->where('type', 'challenge|battle');
    Route::post('/contests/{type}/{id}/reports', [\App\Http\Controllers\ContestReportingController::class, 'store'])
        ->where('type', 'challenge|battle');
    Route::post('/battles/{id}/accept', [\App\Http\Controllers\ContestParticipationController::class, 'accept']);
    Route::post('/battles/{id}/decline', [\App\Http\Controllers\ContestParticipationController::class, 'decline']);
    Route::post('/stories/exclude-useful', ['uses' => 'Api\StoryController@excludeUseful']);
});


Route::get('/app_version', function ($token = null) {
    return response()->json([
        'success' => true,
        'data' => [
            'ios' => get_option('app_ios_version', true),
            'android' => get_option('app_android_version', true),
        ],
    ]);
})->name('unsubscribe');




Route::group(['middleware' => 'update.user.data'], function (): void {

    Route::get('/campaigns/stories', 'Api\ApiController@get_stories');
    Route::get('/stories_list', 'Api\ApiController@get_stories');

});




Route::controller(CampaignsController::class)->prefix('campaign')->group(function (): void {
    Route::get('/', 'index');
    Route::post('{slug}/addLike', 'addLike')->middleware('suspicious.restricted');
    Route::get('{slug}', 'show');
    Route::get('backers/{slug}', 'showBackers');
    Route::get('updates/{slug}', 'showUpdates');
    Route::get('faqs/{slug}', 'showFaqs');
});

Route::get('campaigns', ['as' => 'browse_campaigns', 'uses' => 'CampaignsController@index']);
Route::get('campaigns/filter', ['as' => 'browse_campaigns_filter', 'uses' => 'CampaignsController@browseCampaignsFilter']);
Route::get('campaigns/projects-we-loved', ['as' => 'projects_we_loved', 'uses' => 'CampaignsController@projectsWeLoved']);

Route::get(
    'campaigns/funded-campaigns',
    ['as' => 'recently_funded_campaigns', 'uses' => 'CampaignsController@recentlyFundedCampaigns']
);

Route::any('add-to-cart/{reward_id?}', ['as' => 'add_to_cart_api', 'uses' => 'CampaignsController@addToCart']);
Route::get('contact-us', 'HomeController@contactUs');

Route::post('contact-us', 'HomeController@contactUsPost');

Route::get('offer', 'HomeController@offer');
Route::get('/docs/card_pay', 'HomeController@cardPay');
Route::get('/docs/gift_offer', 'HomeController@giftOffer');
Route::get('/docs/access_offer', 'HomeController@accessOffer');
Route::get('/docs/personal_offer', 'HomeController@personalOffer');
Route::get('/docs/license', 'HomeController@license');
Route::get('/docs/rules', 'HomeController@rules');

// categories
//Route::get('search', ['as' => 'search', 'uses' => 'CampaignsController@search']);
Route::get('search', ['as' => 'search', 'uses' => 'SearchController@search']);

Route::get('category/{slug}', ['as' => 'single_category', 'uses' => 'CategoriesController@singleCategory']);
Route::get('categories/list', ['as' => 'categories_list', 'uses' => 'CategoriesController@categories_list']);
Route::get('countries/list', ['as' => 'countries_list', 'uses' => 'Api\ApiController@countries_list']);

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

// Cookie
Route::post('cookie-accept', ['as' => 'cookie_accept', 'uses' => 'HomeController@acceptCookie']);


Route::get('check-email-uniqueness', [RegisterController::class, 'checkEmailUniqueness']);
Route::get('check-username-uniqueness', [UserController::class, 'checkUsernameUniqueness']);

Route::group(['prefix' => 'login'], function (): void {
//    Route::get('facebook', ['as' => 'facebook_redirect', 'uses' => 'SocialLogin@redirectFacebook']);
//    Route::get('facebook-callback', ['as' => 'facebook_callback', 'uses' => 'SocialLogin@callbackFacebook']);
//
//    Route::get('google', ['as' => 'google_redirect', 'uses' => 'SocialLogin@redirectGoogle']);
//    Route::get('google-callback', ['as' => 'google_callback', 'uses' => 'SocialLogin@callbackGoogle']);
//
//    Route::get('twitter', ['as' => 'twitter_redirect', 'uses' => 'SocialLogin@redirectTwitter']);
//    Route::get('twitter-callback', ['as' => 'twitter_callback', 'uses' => 'SocialLogin@callbackTwitter']);
});

Route::group(['middleware' => 'auth'], function (): void {
    Route::post('payment-comment', [PaymentCommentController::class, 'comment']);
    Route::post('/payments/{payment}/thank', [PaymentCommentController::class, 'thank']);
    Route::post('/payments/thank/moderate', [PaymentCommentController::class, 'moderate']);
});

Route::group(['prefix' => 'services'], function (): void {
    Route::post('moneybox', ['as' => 'services.moneybox', 'uses' => 'Api\ChatGPTController@moneybox']);
    Route::post('copystories', ['as' => 'services.copystories', 'uses' => 'Api\ChatGPTController@copystories']);
    Route::post('/chatgpt/ping', ['as' => 'services.api.ping', 'uses' => 'Api\ChatGPTController@ping']);
    //Route::post('stories_data_by_video', ['as' => 'services.stories_data_by_video', 'uses' => 'Api\ChatGPTController@stories_data_by_video']);
    Route::post('/thanks_generate', ['as' => 'services.api.thanks.web', 'uses' => 'Api\ChatGPTController@thanks']);
    Route::group(['prefix' => 'chatgpt', 'middleware' => ['auth:sanctum', 'update.user.data']], function (): void {
        Route::post('moneybox', ['as' => 'services.api.moneybox', 'uses' => 'Api\ChatGPTController@moneybox']);
        Route::post('copystories', ['as' => 'services.api.copystories', 'uses' => 'Api\ChatGPTController@copystories']);
        Route::post('/thanks', ['as' => 'services.api.thanks', 'uses' => 'Api\ChatGPTController@thanks']);
        //Route::post('stories_data_by_video', ['as' => 'services.api.stories_data_by_video', 'uses' => 'Api\ChatGPTController@stories_data_by_video']);
    });
    Route::post('/chatgpt/moderation/text', ['uses' => 'Api\ChatGPTController@moderation_text']);
    Route::post('/chatgpt/moderation/image', ['uses' => 'Api\ChatGPTController@moderation_image']);
    Route::post('/chatgpt/moderation/video', ['uses' => 'Api\ChatGPTController@moderation_video']);


    Route::post('/chatgpt/assistant/text', ['uses' => 'Api\ChatGPTController@assistant_text']);

    Route::post('deploy', 'UpdateController@deploy');
});

// Dashboard Route
Route::group(['prefix' => 'dashboard', 'middleware' => ['auth:sanctum', 'update.user.data']], function (): void {
    Route::get('/', ['as' => 'dashboard', 'uses' => 'DashboardController@dashboard']);

    Route::get('thanks', [PaymentCommentController::class, 'thankList']);

    Route::get('autopayments', 'PaymentController@autopayments');
    Route::get('autopayments/{id}/delete', 'PaymentController@deleteAutopayment');

    Route::get('action_campaigns', [ActionsController::class, 'getMyActions']);

    Route::group(['prefix' => 'my_campaigns'], function (): void {
        Route::get('/', ['as' => 'my_campaigns', 'uses' => 'CampaignsController@myCampaigns']);
        Route::get('my_pending_campaigns', ['as' => 'my_pending_campaigns', 'uses' => 'CampaignsController@myPendingCampaigns']);

        Route::get('start_campaign', ['as' => 'start_campaign', 'uses' => 'CampaignsController@create']);
        Route::post('start_campaign', ['uses' => 'CampaignsController@store']);

        Route::get('edit_campaign/{id}', ['as' => 'edit_campaign', 'uses' => 'CampaignsController@edit']);
        Route::post('edit_campaign/{id}', ['uses' => 'CampaignsController@update']);
        Route::post('edit_campaign/{id}/story', ['uses' => 'CampaignsController@updateImage']);

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

    Route::group(['prefix' => 'admin_comments'], function (): void {
        Route::get('/', ['as' => 'admin_comments', 'uses' => 'CommentController@index']);
        Route::post('action', ['as' => 'comment_action', 'uses' => 'CommentController@commentAction']);
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

            // Edit
            Route::get('edit/{id}', ['as' => 'users_edit', 'uses' => 'UserController@profileEdit']);
            //Route::post('edit/{id}', ['uses' => 'UserController@profileEditPost']);

            Route::get('delete/{id}', ['as' => 'user_delete', 'uses' => 'UserController@userDeleteByAdmin']);

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
        });

        Route::get('/mailing', ['as' => 'mailing', 'uses' => 'NewsController@mailing']);
        Route::post('/mailing', ['as' => 'mailing_save', 'uses' => 'NewsController@mailingSave']);
    });

    Route::group(['prefix' => 'payments'], function (): void {
        Route::get('/', ['as' => 'payments', 'uses' => 'PaymentController@index']);
        Route::get('pending', ['as' => 'payments_pending', 'uses' => 'PaymentController@paymentsPending']);
        Route::get('view/{id}', ['as' => 'payment_view', 'uses' => 'PaymentController@view']);
        Route::get('status-change/{id}/{status}', ['as' => 'status_change', 'uses' => 'PaymentController@markSuccess']);
    });

    Route::group(['prefix' => 'withdraw'], function (): void {
        Route::get('/', ['as' => 'withdraw', 'uses' => 'PaymentController@withdraw']);
        Route::post('/', ['uses' => 'PaymentController@withdrawRequest']);

        Route::get('view/{id}', ['as' => 'withdraw_request_view', 'uses' => 'PaymentController@withdrawRequestView']);
        Route::post('view/{id}', ['uses' => 'PaymentController@withdrawalRequestsStatusSwitch']);
    });

    Route::get('backed_campaigns', ['as' => 'backed_campaigns', 'uses' => 'PaymentController@backedCampaigns']);


    Route::group(['prefix' => 'media'], function (): void {
        Route::post('upload', ['as' => 'post_media_upload', 'uses' => 'MediaController@store']);
        Route::get('load_filemanager', ['as' => 'load_filemanager', 'uses' => 'MediaController@loadFileManager']);
        Route::post('delete', ['as' => 'delete_media', 'uses' => 'MediaController@delete']);
    });
});
