<?php

declare(strict_types=1);

namespace App\Socialite;

use SocialiteProviders\Manager\SocialiteWasCalled;

class VKontakteExtendSocialite
{
    public function handle(SocialiteWasCalled $socialiteWasCalled): void
    {
        $socialiteWasCalled->extendSocialite('vkontakte', VKontakteProvider::class);
    }
}
