<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\VkAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class DeelsOAuthCompatibilityController extends Controller
{
    private const SESSION_KEY = 'deels_oauth_vk';

    public function redirectToVk(Request $request, VkAuthService $vkAuthService): RedirectResponse
    {
        $returnUrl = $this->safeReturnUrl((string) $request->query('return_url', '/profile'), $request);
        $state = Str::random(48);
        $codeVerifier = bin2hex(random_bytes(48));
        $callbackUrl = route('deels.compat.auth.oauth.vk.callback');

        $link = $vkAuthService->makeAuthLink(
            $callbackUrl,
            null,
            $state,
            $codeVerifier
        );

        $request->session()->put(self::SESSION_KEY, [
            'state' => $state,
            'code_verifier' => $codeVerifier,
            'redirect_uri' => $callbackUrl,
            'return_url' => $returnUrl,
            'created_at' => now()->timestamp,
        ]);

        return redirect()->away((string) $link['auth_url']);
    }

    public function callback(Request $request, VkAuthService $vkAuthService): RedirectResponse
    {
        $pending = $request->session()->pull(self::SESSION_KEY);
        $fallback = url('/login');

        if (!is_array($pending)
            || empty($pending['state'])
            || !hash_equals((string) $pending['state'], (string) $request->query('state', ''))
            || now()->timestamp - (int) ($pending['created_at'] ?? 0) > 600
        ) {
            return redirect($fallback . '?oauth_error=' . rawurlencode('Сессия входа VK устарела. Попробуйте ещё раз.'));
        }

        if (!$request->filled('code') || !$request->filled('device_id')) {
            return redirect($fallback . '?oauth_error=' . rawurlencode('VK не вернул данные для авторизации.'));
        }

        $authRequest = Request::create('/api/vk_auth', 'POST', [
            'code' => (string) $request->query('code'),
            'device_id' => (string) $request->query('device_id'),
            'code_verifier' => (string) $pending['code_verifier'],
            'redirect_uri' => (string) $pending['redirect_uri'],
        ]);
        $authRequest->setLaravelSession($request->session());

        try {
            $auth = $vkAuthService->authenticate($authRequest);
        } catch (\Throwable $e) {
            report($e);
            return redirect($fallback . '?oauth_error=' . rawurlencode('Не удалось войти через VK. Попробуйте ещё раз.'));
        }

        $user = $auth['user'];
        if ($banPayload = $vkAuthService->banPayload($user)) {
            return redirect($fallback . '?oauth_error=' . rawurlencode((string) ($banPayload['error'] ?? 'Аккаунт заблокирован.')));
        }

        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        return redirect()->to((string) $pending['return_url']);
    }

    private function safeReturnUrl(string $returnUrl, Request $request): string
    {
        $returnUrl = trim($returnUrl);
        if ($returnUrl === '' || str_starts_with($returnUrl, '//')) {
            return url('/profile');
        }

        if (str_starts_with($returnUrl, '/')) {
            return url($returnUrl);
        }

        $parts = parse_url($returnUrl);
        if (!is_array($parts) || empty($parts['host'])) {
            return url('/profile');
        }

        $requestHost = strtolower((string) $request->getHost());
        $returnHost = strtolower((string) $parts['host']);
        if (!hash_equals($requestHost, $returnHost)) {
            return url('/profile');
        }

        $scheme = $parts['scheme'] ?? $request->getScheme();
        if (!in_array($scheme, ['http', 'https'], true)) {
            return url('/profile');
        }

        return $returnUrl;
    }
}
