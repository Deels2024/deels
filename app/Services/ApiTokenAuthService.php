<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ApiTokenAuthService
{
    public function __construct(private UserRequestContextService $requestContextService)
    {
    }

    public function createToken(Request $request): array
    {
        $login = $request->input('email');
        $loginField = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $user = User::where($loginField, $login)->first();

        if ($user && ($seconds = app(AccountLoginRateLimiter::class)->blockedFor($user)) > 0) {
            return $this->lockoutResponse($seconds);
        }

        if (!$user || !Hash::check($request->input('password'), $user->password)) {
            if ($user && ($seconds = app(AccountLoginRateLimiter::class)->recordFailure($user)) > 0) {
                return $this->lockoutResponse($seconds);
            }

            return [
                'payload' => [
                    'error' => 'Пользователь не найден или неверный пароль.',
                ],
                'status' => 200,
            ];
        }

        app(AccountLoginRateLimiter::class)->clearFailures($user);

        $user->update([
            'user_data' => $this->requestContextService->build($request),
            'ip_address' => $request->ip(),
        ]);

        $user->tokens()->delete();
        $token = $user->createToken($request->input('device_name'))->plainTextToken;

        $bannedTill = Carbon::parse($user->banned_till);
        if ($user->banned_till && Carbon::now()->lt($bannedTill)) {
            return [
                'payload' => [
                    'error' => 'Ваш аккаунт заблокирован до ' . $bannedTill->format('Y-m-d H:i:s'),
                    'banned' => true,
                    'banned_till' => $bannedTill->toDateTimeString(),
                ],
                'status' => 403,
            ];
        }

        return [
            'payload' => [
                'success' => true,
                'access_token' => $token,
                'token_type' => 'Bearer',
            ],
            'status' => 200,
        ];
    }

    private function lockoutResponse(int $seconds): array
    {
        return [
            'payload' => [
                'error' => 'Слишком много попыток входа. Попробуйте снова позже.',
                'retry_after' => $seconds,
            ],
            'status' => 429,
        ];
    }
}
