<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Campaign;
use App\Models\Challenge;
use App\Models\Games\GameSession;
use App\Models\Mailing;
use App\Models\Message;
use App\Models\Story;
use App\Observers\CampaignObserver;
use App\Observers\ChallengeObserver;
use App\Observers\Games\GameSessionObserver;
use App\Observers\MailingObserver;
use App\Observers\MessageObserver;
use App\Observers\StoryObserver;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\SomeEvent' => [
            'App\Listeners\EventListener',
        ],
        \SocialiteProviders\Manager\SocialiteWasCalled::class => [
            \App\Socialite\VKontakteExtendSocialite::class.'@handle',
            \SocialiteProviders\Yandex\YandexExtendSocialite::class.'@handle',
            \SocialiteProviders\Mailru\MailruExtendSocialite::class.'@handle',
            \SocialiteProviders\Odnoklassniki\OdnoklassnikiExtendSocialite::class.'@handle',
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        parent::boot();
        Story::observe(StoryObserver::class);
        Message::observe(MessageObserver::class);
        Challenge::observe(ChallengeObserver::class);
        Campaign::observe(CampaignObserver::class);
        Mailing::observe(MailingObserver::class);
        GameSession::observe(GameSessionObserver::class);
    }
}
