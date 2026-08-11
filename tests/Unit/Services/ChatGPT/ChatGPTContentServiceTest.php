<?php

declare(strict_types=1);

namespace Tests\Unit\Services\ChatGPT;

use App\Services\ChatGPT\ChatGPTContentService;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Http\Request;
use Tests\Feature\Support\CreatesCharacterizationSchema;
use Tests\TestCase;

class ChatGPTContentServiceTest extends TestCase
{
    use CreatesCharacterizationSchema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createCharacterizationSchema();
        EloquentModel::unsetEventDispatcher();
    }

    public function test_moneybox_returns_missing_user_contract_without_calling_external_service(): void
    {
        $result = (new ChatGPTContentService())->moneybox(Request::create('/chatgpt/moneybox', 'POST', [
            'user_id' => 404,
        ]));

        self::assertSame([
            'success' => false,
            'error' => 'Пользователь не найден',
        ], $result);
    }

    public function test_copystories_returns_missing_user_contract_without_calling_external_service(): void
    {
        $result = (new ChatGPTContentService())->copystories(Request::create('/chatgpt/copystories', 'POST', [
            'user_id' => 404,
        ]));

        self::assertSame([
            'success' => false,
            'error' => 'Пользователь не найден',
        ], $result);
    }
}
