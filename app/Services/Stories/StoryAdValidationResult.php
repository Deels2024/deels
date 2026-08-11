<?php

declare(strict_types=1);

namespace App\Services\Stories;

class StoryAdValidationResult
{
    private function __construct(
        public readonly bool $valid,
        public readonly array $adsData,
        public readonly ?string $error
    ) {
    }

    public static function valid(array $adsData): self
    {
        return new self(true, $adsData, null);
    }

    public static function invalid(string $error, array $adsData): self
    {
        return new self(false, $adsData, $error);
    }
}
