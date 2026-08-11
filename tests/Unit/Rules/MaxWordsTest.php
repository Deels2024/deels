<?php

declare(strict_types=1);

namespace Tests\Unit\Rules;

use App\Rules\MaxWords;
use PHPUnit\Framework\TestCase;

class MaxWordsTest extends TestCase
{
    public function test_it_accepts_up_to_the_word_limit(): void
    {
        $rule = new MaxWords(650);

        self::assertTrue($rule->passes('description', implode(' ', array_fill(0, 650, 'слово'))));
        self::assertFalse($rule->passes('description', implode("\n", array_fill(0, 651, 'слово'))));
    }
}
