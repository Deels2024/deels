<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Stories;

use App\Models\Story;
use App\Services\Stories\StoryDonationService;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Http\Request;
use Tests\Feature\Support\CreatesCharacterizationSchema;
use Tests\TestCase;

class StoryDonationServiceTest extends TestCase
{
    use CreatesCharacterizationSchema;

    private StoryDonationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createCharacterizationSchema();
        EloquentModel::unsetEventDispatcher();
        $this->service = new StoryDonationService();
    }

    public function test_pay_rejects_non_positive_amount(): void
    {
        $request = Request::create('/api/stories/pay/1', 'POST', [
            'amount' => 0,
        ]);

        self::assertSame([
            'success' => false,
            'error' => 'Укажите сумму',
        ], $this->service->pay($request, 1));
    }

    public function test_pay_rejects_missing_story(): void
    {
        $request = Request::create('/api/stories/pay/1', 'POST', [
            'amount' => 10,
            'story_id' => 999,
            'user_id' => 1,
        ]);

        self::assertSame([
            'success' => false,
            'error' => 'Сторис не найдена',
        ], $this->service->pay($request, 1));
    }

    public function test_pay_rejects_missing_user(): void
    {
        $owner = $this->createCharacterizationUserWithWallets(['email' => 'owner-pay@example.test']);
        $story = Story::create([
            'user_id' => $owner->id,
            'amount' => 10,
            'paid' => true,
        ]);
        $request = Request::create('/api/stories/pay/1', 'POST', [
            'amount' => 10,
            'story_id' => $story->id,
            'user_id' => 999,
        ]);

        self::assertSame([
            'success' => false,
            'error' => 'Пользователь не найден',
        ], $this->service->pay($request, 1));
    }
}
