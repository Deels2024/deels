<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use Tests\TestCase;

class RegistrationRateLimitTest extends TestCase
{
    public function test_registration_is_limited_to_ten_attempts_per_day_from_one_ip(): void
    {
        for ($attempt = 1; $attempt <= 10; $attempt++) {
            $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.10'])
                ->postJson('/api/register', [])
                ->assertOk();
        }

        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.10'])
            ->post('/api/register', [])
            ->assertStatus(429)
            ->assertHeader('Content-Type', 'application/json')
            ->assertJson([
                'success' => false,
                'error' => 'Слишком много регистраций с вашего IP. Попробуйте позже',
            ]);
    }

    public function test_registration_limits_are_separate_for_different_ips(): void
    {
        for ($attempt = 1; $attempt <= 10; $attempt++) {
            $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.20'])
                ->postJson('/api/register', [])
                ->assertOk();
        }

        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.21'])
            ->postJson('/api/register', [])
            ->assertOk();
    }
}
