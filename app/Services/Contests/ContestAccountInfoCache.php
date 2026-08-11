<?php

declare(strict_types=1);

namespace App\Services\Contests;

use App\Services\ApiAccountInfoService;

class ContestAccountInfoCache
{
    private array $cache = [];

    public function __construct(private readonly ApiAccountInfoService $accountInfo)
    {
    }

    public function build(?int $userId, bool $justUserInfo = true): ?array
    {
        if (!$userId) {
            return null;
        }

        $key = $userId . ':' . (int) $justUserInfo;
        if (!array_key_exists($key, $this->cache)) {
            $this->cache[$key] = $this->accountInfo->build($userId, $justUserInfo);
        }

        return $this->cache[$key];
    }
}
