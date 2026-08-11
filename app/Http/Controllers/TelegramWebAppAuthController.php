<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class TelegramWebAppAuthController extends Controller
{
    public function authenticate(Request $request): JsonResponse
    {
        $initData = (string) $request->input('init_data', '');
        $botToken = (string) (env('TELEGRAM_WEBAPP_BOT_TOKEN') ?: env('TELEGRAM_BOT_TOKEN', ''));

        if ($initData === '' || $botToken === '') {
            Log::warning('Telegram WebApp auth skipped: missing init data or bot token', [
                'has_init_data' => $initData !== '',
                'has_bot_token' => $botToken !== '',
            ]);

            return response()->json(['success' => false, 'message' => 'Telegram auth data is missing'], 422);
        }

        $data = $this->parseInitData($initData);

        $validationError = $this->getInitDataValidationError($data, $botToken);
        if ($validationError !== null) {
            Log::warning('Telegram WebApp auth failed: invalid init data', [
                'reason' => $validationError,
                'has_hash' => !empty($data['hash']),
                'auth_date' => $data['auth_date'] ?? null,
                'telegram_user_id' => isset($data['user']) ? (json_decode($data['user'], true)['id'] ?? null) : null,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Telegram auth data is invalid',
                'reason' => config('app.debug') ? $validationError : null,
            ], 403);
        }

        $telegramUser = json_decode($data['user'] ?? '', true);
        if (!is_array($telegramUser) || empty($telegramUser['id'])) {
            Log::warning('Telegram WebApp auth failed: missing user payload');

            return response()->json(['success' => false, 'message' => 'Telegram user data is missing'], 422);
        }

        try {
            $user = $this->findOrCreateUser($telegramUser, $request);
        } catch (Throwable $e) {
            Log::error('Telegram WebApp auth failed while saving user', [
                'telegram_id' => $telegramUser['id'] ?? null,
                'message' => $e->getMessage(),
            ]);

            return response()->json(['success' => false, 'message' => 'Telegram user save failed'], 500);
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        return response()->json([
            'success' => true,
            'user_id' => $user->id,
        ]);
    }

    public function status(): JsonResponse
    {
        return response()->json([
            'authenticated' => Auth::check(),
            'user_id' => Auth::id(),
        ]);
    }

    private function parseInitData(string $initData): array
    {
        parse_str($initData, $data);

        return is_array($data) ? $data : [];
    }

    private function getInitDataValidationError(array $data, string $botToken): ?string
    {
        $receivedHash = $data['hash'] ?? null;
        if (!is_string($receivedHash) || $receivedHash === '') {
            return 'missing_hash';
        }

        if (empty($data['auth_date']) || ((int) $data['auth_date']) < now()->subDay()->timestamp) {
            return 'expired_or_missing_auth_date';
        }

        unset($data['hash']);
        ksort($data);

        $checkString = collect($data)
            ->map(fn ($value, $key) => $key . '=' . $value)
            ->implode("\n");

        $secretKey = hash_hmac('sha256', $botToken, 'WebAppData', true);
        $calculatedHash = hash_hmac('sha256', $checkString, $secretKey);

        if (!hash_equals($calculatedHash, $receivedHash)) {
            return 'hash_mismatch';
        }

        return null;
    }

    private function findOrCreateUser(array $telegramUser, Request $request): User
    {
        $telegramId = (string) $telegramUser['id'];
        $username = $this->makeUsername($telegramUser);
        $firstName = trim((string) ($telegramUser['first_name'] ?? ''));
        $lastName = trim((string) ($telegramUser['last_name'] ?? ''));
        $name = trim($firstName . ' ' . $lastName) ?: $username;

        $user = User::withTrashed()->where('telegram_id', $telegramId)->first();

        if (!$user) {
            $user = User::create([
                'name' => $name,
                'last_name' => $lastName ?: null,
                'username' => $username,
                'password' => bcrypt(Str::random(32)),
                'user_type' => 'user',
                'active_status' => 1,
                'is_activated' => 1,
                'is_onboarding' => 1,
                'telegram_id' => $telegramId,
                'avatar' => $telegramUser['photo_url'] ?? null,
                'referral_code' => Str::uuid()->toString(),
                'invite_referral_code' => \Cookie::get('refCode') ?? $request->input('refCode'),
                'ip_address' => $request->ip(),
            ]);

            app(\App\Services\ReferralBonusService::class)->awardForRegistration($user);
        } else {
            if (method_exists($user, 'trashed') && $user->trashed()) {
                $user->restore();
            }

            $user->fill([
                'name' => $user->name ?: $name,
                'last_name' => $user->last_name ?: ($lastName ?: null),
                'username' => $user->username ?: $username,
                'avatar' => $user->avatar ?: ($telegramUser['photo_url'] ?? null),
                'active_status' => 1,
                'is_activated' => 1,
                'ip_address' => $request->ip(),
            ]);

            if (isset($user->is_onboarding) && !$user->is_onboarding) {
                $user->is_onboarding = 1;
            }

            $user->save();
        }

        return $user;
    }

    private function makeUsername(array $telegramUser): string
    {
        $base = $telegramUser['username'] ?? null;
        $base = $base ? preg_replace('/[^A-Za-z0-9_]/', '', (string) $base) : null;
        $base = $base ?: 'tg_' . $telegramUser['id'];
        $username = $base;
        $counter = 1;

        while (User::where('username', $username)
            ->where(function ($query) use ($telegramUser): void {
                $query
                    ->whereNull('telegram_id')
                    ->orWhere('telegram_id', '!=', $telegramUser['id']);
            })
            ->exists()) {
            $username = $base . '_' . $counter;
            $counter++;
        }

        return $username;
    }
}
