<?php

declare(strict_types=1);

namespace Illuminate\Contracts\Validation;

use Closure;

if (!interface_exists(ValidationRule::class)) {
    interface ValidationRule
    {
        public function validate(string $attribute, mixed $value, Closure $fail): void;
    }
}
