<?php

declare(strict_types=1);

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

final class MaxWords implements Rule
{
    public function __construct(private int $max)
    {
    }

    public function passes($attribute, $value): bool
    {
        $words = preg_split('/\s+/u', trim((string) $value), -1, PREG_SPLIT_NO_EMPTY);

        return count($words ?: []) <= $this->max;
    }

    public function message(): string
    {
        return 'Описание должно содержать не более ' . $this->max . ' слов';
    }
}
