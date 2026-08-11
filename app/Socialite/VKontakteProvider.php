<?php

declare(strict_types=1);

namespace App\Socialite;

use GuzzleHttp\RequestOptions;
use Illuminate\Support\Arr;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\InvalidStateException;
use Laravel\Socialite\Two\User;
use RuntimeException;

class VKontakteProvider extends AbstractProvider
{
    protected $scopeSeparator = ' ';

    protected $scopes = ['vkid.personal_info', 'email', 'phone'];

    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase('https://id.vk.com/authorize', $state);
    }

    protected function getTokenUrl()
    {
        return 'https://id.vk.com/oauth2/auth';
    }

    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->post('https://id.vk.com/oauth2/user_info', [
            RequestOptions::HEADERS => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer '.$token,
            ],
            RequestOptions::JSON => [
                'client_id' => $this->clientId,
            ],
        ]);

        $contents = (string) $response->getBody();
        $response = json_decode($contents, true);

        if (!is_array($response)) {
            throw new RuntimeException(sprintf('Invalid JSON response from VK ID: %s', $contents));
        }

        return Arr::get($response, 'user', $response);
    }

    protected function mapUserToObject(array $user)
    {
        $id = Arr::get($user, 'user_id', Arr::get($user, 'id'));
        $firstName = Arr::get($user, 'first_name');
        $lastName = Arr::get($user, 'last_name');

        return (new User())->setRaw($user)->map([
            'id' => $id,
            'nickname' => Arr::get($user, 'nickname'),
            'name' => trim($firstName.' '.$lastName),
            'email' => Arr::get($user, 'email', 'vk_'.$id.'@deels.ru'),
            'avatar' => Arr::get($user, 'avatar', Arr::get($user, 'photo_url')),
        ]);
    }

    public function user()
    {
        if ($this->hasInvalidState()) {
            throw new InvalidStateException();
        }

        $response = $this->getAccessTokenResponse($this->getCode());
        $token = $this->parseAccessToken($response);

        return $this->mapUserToObject($this->getUserByToken($token))
            ->setToken($token)
            ->setRefreshToken($this->parseRefreshToken($response))
            ->setExpiresIn($this->parseExpiresIn($response));
    }

    protected function getTokenFields($code)
    {
        return array_merge(parent::getTokenFields($code), [
            'device_id' => $this->request->input('device_id'),
        ]);
    }

    protected function parseAccessToken($body)
    {
        return Arr::get($body, 'access_token');
    }

    protected function parseRefreshToken($body)
    {
        return Arr::get($body, 'refresh_token');
    }

    protected function parseExpiresIn($body)
    {
        return Arr::get($body, 'expires_in');
    }
}
