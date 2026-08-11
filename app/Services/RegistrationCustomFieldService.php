<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RegistrationCustomFieldService
{
    private const FIELD_NAME = 'contact_url';
    private const BAN_MINUTES = 1440;

    public function isTripped(Request $request): bool
    {
        return trim((string) $request->input(self::FIELD_NAME, '')) !== '';
    }

    public function ban(Request $request): void
    {
        Cache::put($this->cacheKey($request->ip()), true, now()->addMinutes(self::BAN_MINUTES));

        Log::warning('Registration custom field triggered.', [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }

    public function isBanned(Request $request): bool
    {
        return Cache::has($this->cacheKey($request->ip()));
    }

    private function cacheKey(?string $ip): string
    {
        return 'registration_customfield_ban:' . hash('sha256', $ip ?? 'unknown');
    }
}
