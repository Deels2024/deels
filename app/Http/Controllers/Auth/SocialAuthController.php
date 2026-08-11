<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Socialite\VKontakteProvider;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{

    public function vk(Request $request)
    {
        return Socialite::buildProvider(VKontakteProvider::class, config('services.vkontakte'))
            ->enablePKCE()
            ->redirect();
    }


}
