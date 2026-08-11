<?php

declare(strict_types=1);

namespace App;

use App\Models\SocialAccount;
use App\Models\User;
use App\Models\UserActivation;
use App\Services\ReferralBonusService;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as ProviderUser;

class SocialAccountService
{
    public function createOrGetFBUser(ProviderUser $providerUser)
    {
        $account = SocialAccount::whereProvider('facebook')
            ->whereProviderUserId($providerUser->getId())
            ->first();

        if ($account) {
            return $account->user;
        } else {
            $account = new SocialAccount([
                'provider_user_id' => $providerUser->getId(),
                'provider' => 'facebook',
            ]);

            $user = User::whereEmail($providerUser->getEmail())->first();

            if (!$user) {
                $user = User::create([
                    'email' => $providerUser->getEmail(),
                    'name' => $providerUser->getName(),
                    'user_type' => 'user',
                    'active_status' => 1,
                ]);
            }
            $account->user()->associate($user);
            $account->save();

            return $user;
        }
    }

    public function createOrGetVKUser(ProviderUser $providerUser)
    {
        $account = SocialAccount::whereProvider('vkontakte')
            ->whereProviderUserId($providerUser->getId())
            ->first();

        if ($account) {
            return $account->user;
        } else {
            $account = new SocialAccount([
                'provider_user_id' => $providerUser->getId(),
                'provider' => 'vkontakte',
            ]);

            $user = User::whereEmail($providerUser->getEmail())->first();

            if (!$user) {
                $user = User::create([
                    'email' => $providerUser->getEmail(),
                    'name' => $providerUser->getName(),
                    'referral_code' => Str::uuid()->toString(),
                    'user_type' => 'user',
                    'is_activated' => 0,
                    'active_status' => 1,
                ]);
            }
            $account->user()->associate($user);
            $account->save();

            return $user;
        }
    }

    public function createOrGetProviderUser(ProviderUser $providerUser, $provider)
    {
        $account = SocialAccount::whereProvider($provider)
            ->whereProviderUserId($providerUser->getId())
            ->first();

        if ($account && $account->user) {
            if ($provider === 'vkontakte' && !$account->user->vk_id) {
                $account->user->forceFill(['vk_id' => $providerUser->getId()])->save();
            }
            if ($provider === 'vkontakte') {
                $this->syncVkProfile($account->user, $providerUser);
            }

            return $account->user;
        } else {
            $account = new SocialAccount([
                'provider_user_id' => $providerUser->getId(),
                'provider' => $provider,
            ]);

            $providerEmail = $providerUser->getEmail();
            if (!$providerEmail && $provider === 'vkontakte') {
                $providerEmail = 'vk_' . $providerUser->getId() . '@deels.ru';
            }

            $user = null;
            if ($provider === 'vkontakte') {
                $generatedVkEmail = 'vk_' . $providerUser->getId() . '@deels.ru';
                if ($providerEmail === $generatedVkEmail) {
                    $user = User::where('email', $providerEmail)->orderBy('id')->first();
                }

                if (!$user) {
                    $user = User::where('vk_id', $providerUser->getId())->first();
                }
            }

            if (!$user && $providerEmail) {
                $user = User::whereEmail($providerEmail)->first();
            }

            $created = false;
            if (!$user) {
                $vkPhone = $provider === 'vkontakte' ? $this->vkPhone($providerUser) : null;
                $user = User::create([
                    'email' => $providerEmail,
                    'name' => $provider === 'vkontakte'
                        ? ($providerUser->user['first_name'] ?? $providerUser->getName())
                        : $providerUser->getName(),
                    'last_name' => $provider === 'vkontakte' ? ($providerUser->user['last_name'] ?? null) : null,
                    'username' => $provider === 'vkontakte' ? 'vk_' . $providerUser->getId() : null,
                    'referral_code' => Str::uuid()->toString(),
                    'invite_referral_code' => \Cookie::get('refCode'),
                    'user_type' => 'user',
                    'phone' => $vkPhone,
                    'phone_hash' => deels_phone_hash($vkPhone),
                    'vk_id' => $provider === 'vkontakte' ? $providerUser->getId() : null,
                    'is_activated' => $provider === 'vkontakte' && $vkPhone ? 1 : 0,
                    'active_status' => 1,
                    'gender' => $provider === 'vkontakte' ? $this->vkGender($providerUser) : null,
                    'avatar' => $provider === 'vkontakte' ? $providerUser->getAvatar() : null,
                ]);
                $created = true;
            } elseif ($provider === 'vkontakte' && !$user->vk_id) {
                $user->forceFill(['vk_id' => $providerUser->getId()])->save();
            }
            if ($provider === 'vkontakte') {
                $this->syncVkProfile($user, $providerUser);
            }
            $account->user()->associate($user);
            $account->save();

            if ($provider !== 'vkontakte' && $user->phone) {
                $activation = ['user_id' => $user->id, 'type' => 'phone', 'phone' => $user->phone, 'token' => null, 'created_at' => now()];
                $activation_item = UserActivation::updateOrCreate($activation);
                $user_helper = new \App\Helpers\UserHelper;
                $response = $user_helper->sendSMSCodeVerifyPhone($activation_item);
            }

            if ($created) {
                app(ReferralBonusService::class)->awardForRegistration($user);
            }

            return $user;
        }
    }

    private function syncVkProfile(User $user, ProviderUser $providerUser): void
    {
        $phone = $this->vkPhone($providerUser);
        $updates = [
            'vk_id' => $user->vk_id ?: $providerUser->getId(),
            'name' => ($providerUser->user['first_name'] ?? null) ?: ($providerUser->getName() ?: $user->name),
            'last_name' => $providerUser->user['last_name'] ?? $user->last_name,
            'gender' => $this->vkGender($providerUser) ?: $user->gender,
            'avatar' => $providerUser->getAvatar() ?: $user->avatar,
        ];

        if ($phone) {
            $updates['phone'] = $phone;
            $updates['phone_hash'] = deels_phone_hash($phone);
            $updates['is_activated'] = 1;
        }

        $user->forceFill(array_filter($updates, static fn ($value) => $value !== null && $value !== ''))->save();

        if ($phone) {
            UserActivation::updateOrCreate(
                ['user_id' => $user->id, 'type' => 'phone'],
                [
                    'phone' => $phone,
                    'token' => null,
                    'is_verified' => true,
                    'verify_phone_data' => [
                        'source' => 'vkontakte',
                        'verified_at' => now()->toDateTimeString(),
                    ],
                ]
            );
        }
    }

    private function vkPhone(ProviderUser $providerUser): ?string
    {
        return deels_normalize_phone(
            $providerUser->user['phone']
            ?? $providerUser->user['default_phone']['number']
            ?? $providerUser->user['default_phone']
            ?? null
        );
    }

    private function vkGender(ProviderUser $providerUser): ?string
    {
        $sex = $providerUser->user['sex'] ?? $providerUser->user['gender'] ?? null;

        if ($sex === 2 || $sex === '2' || $sex === 'male') {
            return 'male';
        }

        if ($sex === 1 || $sex === '1' || $sex === 'female') {
            return 'female';
        }

        return null;
    }


    public function createOrGetGoogleUser(ProviderUser $providerUser)
    {
        $account = SocialAccount::whereProvider('google')
            ->whereProviderUserId($providerUser->getId())
            ->first();
        if ($account) {
            return $account->user;
        } else {
            $account = new SocialAccount([
                'provider_user_id' => $providerUser->getId(),
                'provider' => 'google',
            ]);

            $user = User::whereEmail($providerUser->getEmail())->first();

            if (!$user) {
                $user = User::create([
                    'email' => $providerUser->getEmail(),
                    'name' => $providerUser->getName(),
                    'user_type' => 'user',
                    'active_status' => 1,
                ]);
            }

            $account->user()->associate($user);
            $account->save();

            return $user;
        }
    }

    public function createOrGetTwitterUser(ProviderUser $providerUser)
    {
        $account = SocialAccount::whereProvider('twitter')->whereProviderUserId($providerUser->getId())->first();
        if ($account) {
            if (!$account->user) {
                // Delete social table account if user is not exists
                $account->delete();
            }

            return $account->user;
        } else {
            $account = new SocialAccount([
                'provider_user_id' => $providerUser->getId(),
                'provider' => 'twitter',
            ]);

            $user = User::whereEmail($providerUser->getEmail())->first();

            if (!$user) {
                $avatar_url = $providerUser->getAvatar();
                if (!empty($providerUser->user['profile_image_url_https'])) {
                    $avatar_url = $providerUser->user['profile_image_url_https'];
                }
                $user = User::create([
                    'email' => $providerUser->getEmail(),
                    'name' => $providerUser->getName(),
                    'avatar' => $avatar_url,
                    'user_type' => 'user',
                    'active_status' => '1',
                ]);
            }

            $account->user()->associate($user);
            $account->save();

            return $user;
        }
    }
}
