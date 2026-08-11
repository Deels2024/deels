<?php

declare(strict_types=1);

namespace App\Rules;

use EragLaravelDisposableEmail\Rules\DisposableEmailRule;
use Illuminate\Contracts\Validation\Rule;

final class DisposableEmail implements Rule
{
    public const MESSAGE = 'Данный e-mail заблокирован. Используйте другой адрес для регистрации';

    public function passes($attribute, $value): bool
    {
        return !DisposableEmailRule::isDisposable(strtolower(trim((string) $value)));
    }

    public function message(): string
    {
        return self::MESSAGE;
    }
}
