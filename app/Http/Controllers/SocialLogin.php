<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\SocialAccountService;
use App\Services\VkAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialLogin extends Controller
{
    public function redirectFacebook()
    {
        return Socialite::driver('facebook')->redirect();
    }

    public function redirectVK(VkAuthService $vkAuthService)
    {
        $redirectUri = config('services.vkontakte.redirect');
        $codeVerifier = bin2hex(random_bytes(48));
        $state = Crypt::encryptString(json_encode([
            'code_verifier' => $codeVerifier,
            'redirect_uri' => $redirectUri,
            'nonce' => Str::random(40),
        ], JSON_UNESCAPED_SLASHES));

        $link = $vkAuthService->makeAuthLink($redirectUri, null, $state, $codeVerifier);

        return redirect()->away($link['auth_url']);
    }

    public function callbackVK(Request $request, VkAuthService $vkAuthService)
    {
        try {
            $state = $this->decryptVkState((string) $request->input('state'));
            $request->merge([
                'code_verifier' => $state['code_verifier'],
                'redirect_uri' => $state['redirect_uri'],
            ]);

            $auth = $vkAuthService->authenticate($request);
            $user = $auth['user'];

            if ($banPayload = $vkAuthService->banPayload($user)) {
                return redirect(route('login'))->with('error', $banPayload['error']);
            }

            auth()->login($user);

            return redirect()->intended(route('dashboard'));
        } catch (\Exception $e) {
            return redirect(route('login'))->with('error', $e->getMessage());
        }
    }

    private function decryptVkState(string $state): array
    {
        if (!$state) {
            throw new \RuntimeException('Invalid VK auth state.');
        }

        $payload = json_decode(Crypt::decryptString($state), true);
        if (
            !is_array($payload)
            || empty($payload['code_verifier'])
            || empty($payload['redirect_uri'])
        ) {
            throw new \RuntimeException('Invalid VK auth state.');
        }

        return $payload;
    }

    public function redirectYandex()
    {
        return Socialite::driver('yandex')->redirect();
    }

    public function callbackYandex(SocialAccountService $service)
    {
        try {
            $social_user = Socialite::driver('yandex')->user();
            $user = $service->createOrGetProviderUser($social_user, 'yandex');
            auth()->login($user);

            return redirect()->intended(route('dashboard'));
        } catch (\Exception $e) {
            return redirect(route('login'))->with('error', $e->getMessage());
        }
    }

    public function redirectMailru()
    {
        return Socialite::driver('mailru')->redirect();
    }

    public function callbackMailru(SocialAccountService $service)
    {
        try {
            $social_user = Socialite::driver('mailru')->user();
            $user = $service->createOrGetProviderUser($social_user, 'mailru');
            auth()->login($user);

            return redirect()->intended(route('dashboard'));
        } catch (\Exception $e) {
            return redirect(route('login'))->with('error', $e->getMessage());
        }
    }

    public function redirectOK()
    {
        return Socialite::driver('odnoklassniki')->redirect();
    }

    public function callbackOK(SocialAccountService $service)
    {
        try {
            $social_user = Socialite::driver('odnoklassniki')->user();
            $user = $service->createOrGetProviderUser($social_user, 'odnoklassniki');
            auth()->login($user);

            return redirect()->intended(route('dashboard'));
        } catch (\Exception $e) {
            return redirect(route('login'))->with('error', $e->getMessage());
        }
    }


    public function callbackFacebook(SocialAccountService $service)
    {
        try {
            $fb_user = Socialite::driver('facebook')->user();
            $user = $service->createOrGetFBUser($fb_user);
            auth()->login($user);

            return redirect()->intended(route('dashboard'));
        } catch (\Exception $e) {
            return redirect(route('login'))->with('error', $e->getMessage());
        }
    }

    public function redirectGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callbackGoogle(SocialAccountService $service)
    {
        try {
            $fb_user = Socialite::driver('google')->user();
            $user = $service->createOrGetGoogleUser($fb_user);
            auth()->login($user);

            return redirect()->intended(route('dashboard'));
        } catch (\Exception $e) {
            return redirect(route('login'))->with('error', $e->getMessage());
        }
    }

    public function redirectTwitter()
    {
        return Socialite::driver('twitter')->redirect();
    }

    public function callbackTwitter(SocialAccountService $service)
    {
        try {
            $twitter_user = Socialite::driver('twitter')->user();
            $user = $service->createOrGetTwitterUser($twitter_user);
            if (!$user) {
                return redirect(route('twitter_redirect'));
            }
            auth()->login($user);

            return redirect()->intended(route('dashboard'));
        } catch (\Exception $e) {
            // return $e->getMessage();
            return redirect(route('login'))->with('error', $e->getMessage());
        }
    }
}
