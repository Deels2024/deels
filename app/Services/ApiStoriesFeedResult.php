<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ApiStoriesFeedResult
{
    public function __construct(
        public readonly LengthAwarePaginator $media,
        public readonly ?User $user,
        public readonly mixed $userId,
        public readonly array $excludeIds,
        public readonly int $requestedPage
    ) {
    }
}
