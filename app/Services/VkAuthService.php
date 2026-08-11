<?php

declare(strict_types=1);

namespace App\Services;

use App\Http\Controllers\Api\ContactsController;
use App\Models\FriendSuggestion;
use App\Models\SocialAccount;
use App\Models\User;
use App\Models\UserActivation;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class VkAuthService
{
    public function makeAuthLink(
        ?string $redirectUri = null,
        ?string $scope = null,
        ?string $state = null,
        ?string $codeVerifier = null
    ): array
    {
        $codeVerifier = $codeVerifier ?: bin2hex(random_bytes(48));
        $codeChallenge = rtrim(strtr(base64_encode(hash('sha256', $codeVerifier, true)), '+/', '-_'), '=');
        $state = $state ?: Str::random(40);
        $redirectUri = $redirectUri ?: 'https://oauth.vk.com/blank.html';
        $scope = $scope ?: 'vkid.personal_info email phone friends';

        $authUrl = 'https://id.vk.com/authorize?' . http_build_query([
            'client_id' => config('services.vkontakte.client_id'),
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => $scope,
            'state' => $state,
            'code_challenge' => $codeChallenge,
            'code_challenge_method' => 'S256',
        ], '', '&', PHP_QUERY_RFC3986);

        return [
            'auth_url' => $authUrl,
            'state' => $state,
            'code_verifier' => $codeVerifier,
            'redirect_uri' => $redirectUri,
            'scope' => $scope,
        ];
    }

    public function authenticate(Request $request): array
    {
        $vkToken = $request->input('vk_token');
        $client = new Client();

        try {
            $vkToken = $vkToken ?: $this->exchangeVkIdCodeForToken($client, $request);
            $vkUser = $this->getVkIdUser($client, $vkToken);

            Log::info(['token' => $vkToken]);
        } catch (\Exception $e) {
            if (!$request->filled('vk_token')) {
                Log::error('VK ID auth failed: ' . $e->getMessage());

                throw $e;
            }

            try {
                $vkUser = $this->getLegacyVkUser($client, $vkToken);
            } catch (\Exception $legacyException) {
                Log::error('VK user retrieval failed: ' . $e->getMessage());
                Log::error('Legacy VK user retrieval failed: ' . $legacyException->getMessage());

                throw $legacyException;
            }
        }

        $user = $this->createOrUpdateUser($vkUser);
        $vkFriendIds = $this->getVkFriendIds($client, $vkToken);

        if ($vkFriendIds) {
            app(ContactsController::class)->syncVkFriends($user, $vkFriendIds);
        }

        return [
            'user' => $user,
            'vk_user' => $vkUser,
            'vk_friend_ids' => $vkFriendIds,
            'found_users' => app(ContactsController::class)->getSuggestedUsers($user),
            'show_friends_popup' => !empty($vkFriendIds) && FriendSuggestion::where('user_id', $user->id)
                ->whereNull('followed_at')
                ->exists(),
        ];
    }

    public function banPayload(User $user): ?array
    {
        if (!$user->banned_till) {
            return null;
        }

        $bannedTill = Carbon::parse($user->banned_till);
        if (Carbon::now()->gte($bannedTill)) {
            return null;
        }

        return [
            'error' => 'Ваш аккаунт заблокирован до ' . $bannedTill->format('Y-m-d H:i:s'),
            'banned' => true,
            'banned_till' => $bannedTill->toDateTimeString(),
        ];
    }

    private function getVkIdUser(Client $client, string $vkToken): array
    {
        $userResponse = $client->post('https://id.vk.com/oauth2/user_info', [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $vkToken,
            ],
            'json' => [
                'client_id' => config('services.vkontakte.client_id'),
            ],
        ]);

        $userBody = json_decode($userResponse->getBody()->getContents(), true);

        if (!is_array($userBody)) {
            throw new \RuntimeException('Invalid VK ID response.');
        }

        $vkUser = $userBody['user'] ?? $userBody;
        $vkUser['id'] = $vkUser['user_id'] ?? $vkUser['id'] ?? null;
        $vkUser['photo_200'] = $vkUser['avatar'] ?? $vkUser['photo_url'] ?? null;
        $vkUser['phone'] = $this->vkPhone($vkUser);
        $vkUser['gender'] = $this->vkGender($vkUser);

        if (!$vkUser['id']) {
            throw new \RuntimeException('Unable to retrieve user details from VK ID.');
        }

        return $vkUser;
    }

    private function createOrUpdateUser(array $vkUser): User
    {
        $vkId = (string) $vkUser['id'];
        $firstName = trim((string) ($vkUser['first_name'] ?? '')) ?: ('vk_' . $vkId);
        $lastName = trim((string) ($vkUser['last_name'] ?? ''));
        $vkEmail = filter_var($vkUser['email'] ?? null, FILTER_VALIDATE_EMAIL) ?: null;
        $user = User::where('vk_id', $vkId)->first();

        if (!$user && $vkEmail) {
            $user = User::where('email', $vkEmail)->first();
        }

        if ($user && !$vkEmail && $user->email === 'vk_' . $vkId . '@deels.ru') {
            $user->forceFill(['email' => null])->save();
        }

        if (!$user) {
            $user = User::create([
                'vk_id' => $vkId,
                'name' => $firstName,
                'last_name' => $lastName ?: null,
                'email' => $vkEmail,
                'username' => $this->makeUniqueUsername($firstName, $lastName, $vkId),
                'password' => bcrypt(Str::random(16)),
                'avatar' => $vkUser['photo_200'] ?? null,
                'gender' => $vkUser['gender'] ?? null,
                'phone' => $vkUser['phone'] ?? null,
                'phone_hash' => deels_phone_hash($vkUser['phone'] ?? null),
                'is_activated' => !empty($vkUser['phone']),
                'referral_code' => Str::uuid()->toString(),
            ]);
        } elseif (!$user->vk_id) {
            $user->forceFill(['vk_id' => $vkId])->save();
        }

        $this->syncVkProfile($user, $vkUser);
        $this->syncSocialAccount($user, $vkId);

        return $user;
    }

    private function makeUniqueUsername(string $firstName, string $lastName, string $vkId): string
    {
        $base = trim(Str::slug(Str::ascii(trim($firstName . ' ' . $lastName)), '_'), '_');
        $base = mb_substr($base ?: 'vk_' . $vkId, 0, 60);
        $username = $base;
        $counter = 2;

        while (User::withTrashed()->where('username', $username)->exists()) {
            $suffix = '_' . $counter++;
            $username = mb_substr($base, 0, 60 - mb_strlen($suffix)) . $suffix;
        }

        return $username;
    }

    private function syncSocialAccount(User $user, string $vkId): void
    {
        $account = SocialAccount::whereProvider('vkontakte')
            ->whereProviderUserId($vkId)
            ->first();

        if (!$account) {
            $account = new SocialAccount([
                'provider_user_id' => $vkId,
                'provider' => 'vkontakte',
            ]);
        }

        $account->user()->associate($user);
        $account->save();
    }

    private function syncVkProfile(User $user, array $vkUser): void
    {
        $updates = [
            'vk_id' => $user->vk_id ?: ($vkUser['id'] ?? null),
            'name' => $vkUser['first_name'] ?? $user->name,
            'last_name' => $vkUser['last_name'] ?? $user->last_name,
            'avatar' => $vkUser['photo_200'] ?? $user->avatar,
            'gender' => $vkUser['gender'] ?? $user->gender,
        ];

        if (!empty($vkUser['phone'])) {
            $updates['phone'] = $vkUser['phone'];
            $updates['phone_hash'] = deels_phone_hash($vkUser['phone']);
            $updates['is_activated'] = 1;
        }

        if (empty($user->email) && !empty($vkUser['email']) && filter_var($vkUser['email'], FILTER_VALIDATE_EMAIL)) {
            $updates['email'] = $vkUser['email'];
        }

        $user->forceFill(array_filter($updates, static fn ($value) => $value !== null && $value !== ''))->save();

        if (!empty($vkUser['phone'])) {
            UserActivation::updateOrCreate(
                ['user_id' => $user->id, 'type' => 'phone'],
                [
                    'phone' => $vkUser['phone'],
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

    private function getVkFriendIds(Client $client, string $vkToken): array
    {
        try {
            $response = $client->get('https://api.vk.com/method/friends.get', [
                'query' => [
                    'access_token' => $vkToken,
                    'v' => '5.131',
                ],
            ]);

            $body = json_decode($response->getBody()->getContents(), true);
            $items = $body['response']['items'] ?? [];

            if (!is_array($items)) {
                return [];
            }

            return array_values(array_unique(array_map('strval', $items)));
        } catch (\Throwable $e) {
            Log::info('VK friends import skipped: ' . $e->getMessage());

            return [];
        }
    }

    private function vkPhone(array $vkUser): ?string
    {
        return deels_normalize_phone(
            $vkUser['phone']
            ?? $vkUser['default_phone']['number']
            ?? $vkUser['default_phone']
            ?? null
        );
    }

    private function vkGender(array $vkUser): ?string
    {
        $sex = $vkUser['sex'] ?? $vkUser['gender'] ?? null;

        if ($sex === 2 || $sex === '2' || $sex === 'male') {
            return 'male';
        }

        if ($sex === 1 || $sex === '1' || $sex === 'female') {
            return 'female';
        }

        return null;
    }

    private function exchangeVkIdCodeForToken(Client $client, Request $request): string
    {
        try {
            $response = $client->post('https://id.vk.com/oauth2/auth', [
                RequestOptions::HEADERS => [
                    'Accept' => 'application/json',
                ],
                RequestOptions::FORM_PARAMS => [
                    'grant_type' => 'authorization_code',
                    'client_id' => config('services.vkontakte.client_id'),
                    'client_secret' => config('services.vkontakte.client_secret'),
                    'redirect_uri' => $request->input('redirect_uri', config('services.vkontakte.redirect')),
                    'code' => $request->input('code'),
                    'code_verifier' => $request->input('code_verifier'),
                    'device_id' => $request->input('device_id'),
                ],
            ]);
        } catch (RequestException $e) {
            $body = $e->hasResponse() ? (string) $e->getResponse()->getBody() : $e->getMessage();
            Log::error('VK ID token exchange failed: ' . $body);

            throw new \RuntimeException($body, $e->getCode(), $e);
        }

        $body = json_decode($response->getBody()->getContents(), true);

        if (!isset($body['access_token'])) {
            throw new \RuntimeException(json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }

        return $body['access_token'];
    }

    private function getLegacyVkUser(Client $client, string $vkToken): array
    {
        $validationResponse = $client->get('https://api.vk.com/method/secure.checkToken', [
            'query' => [
                'token' => $vkToken,
                'access_token' => config('services.vkontakte.service_key') ?: env('VKONTAKTE_SERVICE_KEY'),
                'v' => '5.131',
            ],
        ]);

        $validationBody = json_decode($validationResponse->getBody()->getContents(), true);

        if (!isset($validationBody['response']) || (int) $validationBody['response']['success'] !== 1) {
            throw new \RuntimeException('Invalid legacy VK token.');
        }

        $userResponse = $client->get('https://api.vk.com/method/users.get', [
            'query' => [
                'user_ids' => $validationBody['response']['user_id'],
                'fields' => 'id,first_name,last_name,photo_200',
                'access_token' => config('services.vkontakte.service_key') ?: env('VKONTAKTE_SERVICE_KEY'),
                'v' => '5.131',
            ],
        ]);

        $userBody = json_decode($userResponse->getBody()->getContents(), true);

        if (!isset($userBody['response'][0])) {
            throw new \RuntimeException('Unable to retrieve legacy VK user.');
        }

        return $userBody['response'][0];
    }
}
