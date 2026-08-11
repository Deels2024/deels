<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Messages;

use App\Services\Messages\MessageTextFormatter;
use Tests\TestCase;

class MessageTextFormatterTest extends TestCase
{
    public function test_plain_text_removes_links_and_html_tags(): void
    {
        $formatter = new MessageTextFormatter();

        self::assertSame(
            'Hello world',
            $formatter->plainText('Hello <a href="https://example.test">hidden</a><b>world</b>')
        );
    }
}
