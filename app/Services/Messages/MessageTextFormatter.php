<?php

declare(strict_types=1);

namespace App\Services\Messages;

class MessageTextFormatter
{
    public function plainText(?string $input): string
    {
        $input = preg_replace('/<a\b[^>]*>.*?<\/a>/i', '', (string) $input);

        return strip_tags(trim($input));
    }
}
