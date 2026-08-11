<?php

declare(strict_types=1);

namespace App\Services\ChatGPT;

use App\Helpers\ChatGPTHelper;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ChatGPTContentService
{
    public function ping(): array
    {
        $response = (new ChatGPTHelper())->ping();

        return [
            'success' => true,
            'message' => $response['message'] ?? null,
        ];
    }

    public function moneybox(Request $request): array
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return [
                'success' => false,
                'error' => 'Пользователь не найден',
            ];
        }

        $limits = $user->limits['chatgpt']['tries'] ?? 5;
        $paymentError = $this->payIfLimitExceeded($user, $limits);
        if ($paymentError) {
            return $paymentError;
        }

        $response = (new ChatGPTHelper())->moneybox(
            $request->input('category'),
            $request->input('name'),
            $request->input('description')
        );

        try {
            $limits = $this->decrementLimits($user, $limits, false);
            $isError = isset($response['detail']) || isset($response['error']);

            return [
                'success' => !$isError,
                'tries' => $limits,
                'name' => $response['name'] ?? null,
                'description' => $response['description'] ?? null,
                'error' => $isError ? ($response['detail'] ?? $response['error'] ?? 'Unknown error') : null,
            ];
        } catch (\Throwable $e) {
            Log::info($e->getMessage());

            return [
                'success' => false,
                'error' => 'Сервис временно недоступен. Если ошибка будет повторяться - свяжитесь с администрацией.',
                'line' => $e->getLine(),
            ];
        }
    }

    public function copystories(Request $request): array
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return [
                'success' => false,
                'error' => 'Пользователь не найден',
            ];
        }

        $limits = $user->limits['chatgpt']['tries'] ?? 5;
        $paymentError = $this->payIfLimitExceeded($user, $limits);
        if ($paymentError) {
            return $paymentError;
        }

        try {
            $response = (new ChatGPTHelper())->copystories($request->input('description'));
            $limits = $this->decrementLimits($user, $limits, true);

            return [
                'success' => true,
                'tries' => $limits,
                'description' => $response['description'],
                'scenario' => $response['scenario'],
            ];
        } catch (\Throwable $e) {
            Log::info('ChatGPTController@copystories: ' . $e->getMessage());

            return [
                'success' => false,
                'data' => $e->getMessage(),
                'error' => 'Сервис временно недоступен. Если ошибка будет повторяться - свяжитесь с администрацией.',
            ];
        }
    }

    private function resolveUser(Request $request): ?User
    {
        return User::find($request->input('user_id')) ?? Auth::user() ?? auth()->user() ?? null;
    }

    private function payIfLimitExceeded(User $user, int $limits): ?array
    {
        if ($limits > 0) {
            return null;
        }

        try {
            $user->wallet_withdraw(intval(env('AI_STORAGE_COST', 50)), [
                'donate' => 'ai',
                'description' => 'Оплата за использование ИИ',
            ]);
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error' => 'Недостаточно дилсов. Необходимо пополнить баланс на ' . env('AI_STORAGE_COST', 50) . ' дилсов.',
            ];
        }

        return null;
    }

    private function decrementLimits(User $user, int $limits, bool $withNextUsage): int
    {
        $limits--;
        if ($limits < 0) {
            $limits = 0;
        }

        $chatGptLimits = [
            'tries' => $limits,
            'last_usage' => Carbon::now(),
        ];
        if ($withNextUsage) {
            $chatGptLimits['next_usage'] = Carbon::now()->addDays(3);
        }

        $user->limits = ['chatgpt' => $chatGptLimits];
        $user->save();

        return $limits;
    }
}
