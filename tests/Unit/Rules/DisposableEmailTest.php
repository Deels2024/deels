<?php

declare(strict_types=1);

namespace Tests\Unit\Rules;

use App\Rules\DisposableEmail;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class DisposableEmailTest extends TestCase
{
    public function test_it_rejects_disposable_email_with_product_message(): void
    {
        $validator = Validator::make(
            ['email' => 'USER@TEMP-MAIL.COM'],
            ['email' => [new DisposableEmail()]]
        );

        $this->assertTrue($validator->fails());
        $this->assertSame(DisposableEmail::MESSAGE, $validator->errors()->first('email'));
    }

    public function test_it_accepts_regular_email(): void
    {
        $validator = Validator::make(
            ['email' => 'user@yandex.ru'],
            ['email' => [new DisposableEmail()]]
        );

        $this->assertFalse($validator->fails());
    }

    public function test_registration_email_code_endpoint_rejects_disposable_email(): void
    {
        $this->postJson('/user/sendEmailCode', [
            'email' => 'user@temp-mail.ru',
            'contact_url' => '',
        ])->assertStatus(422)->assertExactJson([
            'success' => false,
            'error' => DisposableEmail::MESSAGE,
        ]);
    }

    public function test_email_availability_endpoint_returns_disposable_email_message(): void
    {
        $this->getJson('/check-email-uniqueness?email=user%40temp-mail.ru')
            ->assertOk()
            ->assertExactJson([
                'email_exists' => false,
                'error' => DisposableEmail::MESSAGE,
            ]);
    }
}
