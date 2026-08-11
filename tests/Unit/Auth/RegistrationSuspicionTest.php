<?php

declare(strict_types=1);

namespace Tests\Unit\Auth;

use App\Http\Controllers\Auth\RegisterController;
use PHPUnit\Framework\TestCase;

class RegistrationSuspicionTest extends TestCase
{
    /** @dataProvider registrationTelemetryProvider */
    public function test_it_classifies_registration_telemetry(array $telemetry, bool $expected): void
    {
        $controller = new class extends RegisterController {
            public function suspicious(array $data): bool
            {
                return $this->isSuspiciousRegistration($data);
            }
        };

        self::assertSame($expected, $controller->suspicious($telemetry));
    }

    public function registrationTelemetryProvider(): array
    {
        return [
            'missing telemetry' => [[], true],
            'fast with few keys' => [[
                'registration_fill_time_ms' => 4999,
                'registration_keypress_count' => 2,
            ], true],
            'five seconds is acceptable' => [[
                'registration_fill_time_ms' => 5000,
                'registration_keypress_count' => 0,
            ], false],
            'fast with paste' => [[
                'registration_fill_time_ms' => 1000,
                'registration_paste_insert_count' => 1,
            ], false],
            'fast with many keys' => [[
                'registration_fill_time_ms' => 1000,
                'registration_keypress_count' => 3,
            ], false],
            'fast with browser autofill' => [[
                'registration_fill_time_ms' => 1000,
                'registration_browser_autofill' => true,
            ], false],
        ];
    }
}
