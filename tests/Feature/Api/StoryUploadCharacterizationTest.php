<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Battle;
use App\Models\Challenge;
use App\Models\User;
use App\Services\Stories\StoryParticipationPaymentService;
use Laravel\Sanctum\Sanctum;
use Tests\Feature\Support\CreatesCharacterizationSchema;
use Tests\TestCase;

class StoryUploadCharacterizationTest extends TestCase
{
    use CreatesCharacterizationSchema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createCharacterizationSchema();

        Sanctum::actingAs(User::create([
            'name' => 'Story user',
            'username' => 'story-user',
            'email' => 'story-user@example.test',
        ]));
    }

    public function test_store_returns_json_error_when_content_is_missing(): void
    {
        $response = $this
            ->withHeaders(['Accept' => 'application/json'])
            ->postJson('/api/stories/store', ['amount' => 0]);

        $response
            ->assertOk()
            ->assertExactJson([
                'success' => false,
                'error' => 'Отсутствует контент',
            ]);
    }

    public function test_store_rejects_ad_story_for_challenge_before_file_processing(): void
    {
        $response = $this
            ->withHeaders(['Accept' => 'application/json'])
            ->postJson('/api/stories/store', [
                'is_ad' => 1,
                'challenge_id' => 10,
                'ads_data' => [
                    'advertiser' => 'ACME',
                    'erid' => 'erid-1',
                ],
            ]);

        $response
            ->assertOk()
            ->assertExactJson([
                'success' => false,
                'error' => 'Нельзя добавить в челлендж сторис с рекламой!',
            ]);
    }

    public function test_store_requires_advertiser_for_ad_story(): void
    {
        $response = $this
            ->withHeaders(['Accept' => 'application/json'])
            ->postJson('/api/stories/store', [
                'is_ad' => 1,
                'ads_data' => [
                    'erid' => 'erid-1',
                ],
            ]);

        $response
            ->assertOk()
            ->assertExactJson([
                'success' => false,
                'error' => 'Вы не указали рекламодателя!',
            ]);
    }

    public function test_store_requires_erid_or_get_erid_for_ad_story(): void
    {
        $response = $this
            ->withHeaders(['Accept' => 'application/json'])
            ->postJson('/api/stories/store', [
                'is_ad' => 1,
                'ads_data' => [
                    'advertiser' => 'ACME',
                ],
            ]);

        $response
            ->assertOk()
            ->assertExactJson([
                'success' => false,
                'error' => 'Вы не указали ерид!',
            ]);
    }

    public function test_store_does_not_attempt_challenge_payment_when_challenge_is_invalid(): void
    {
        $challenge = Challenge::withoutEvents(fn () => Challenge::create([
            'user_id' => auth()->id() + 1,
            'title' => 'Inactive challenge',
            'active' => false,
            'cost' => 100,
        ]));

        $this->mock(StoryParticipationPaymentService::class, function ($mock): void {
            $mock->shouldNotReceive('payForChallengeIfNeeded');
            $mock->shouldNotReceive('payForBattleIfNeeded');
        });

        $response = $this
            ->withHeaders(['Accept' => 'application/json'])
            ->postJson('/api/stories/store', [
                'media_id' => 1,
                'challenge_id' => $challenge->id,
                'amount' => 0,
            ]);

        $response
            ->assertOk()
            ->assertExactJson([
                'success' => false,
                'error' => 'Челлендж не найден или не активен',
            ]);
    }

    public function test_store_does_not_attempt_battle_payment_when_battle_is_invalid(): void
    {
        $battle = Battle::withoutEvents(fn () => Battle::create([
            'user_id' => auth()->id() + 1,
            'title' => 'Inactive battle',
            'active' => false,
            'cost' => 100,
        ]));

        $this->mock(StoryParticipationPaymentService::class, function ($mock): void {
            $mock->shouldNotReceive('payForChallengeIfNeeded');
            $mock->shouldNotReceive('payForBattleIfNeeded');
        });

        $response = $this
            ->withHeaders(['Accept' => 'application/json'])
            ->postJson('/api/stories/store', [
                'media_id' => 1,
                'battle_id' => $battle->id,
                'amount' => 0,
            ]);

        $response
            ->assertOk()
            ->assertExactJson([
                'success' => false,
                'error' => 'Батл не найден или не активен',
            ]);
    }

    public function test_non_author_cannot_add_useful_story_to_challenge(): void
    {
        $challenge = Challenge::withoutEvents(fn () => Challenge::create([
            'user_id' => auth()->id() + 1,
            'title' => 'Another author challenge',
            'active' => true,
        ]));

        $response = $this
            ->withHeaders(['Accept' => 'application/json'])
            ->postJson('/api/stories/store', [
                'media_id' => 1,
                'challenge_id' => $challenge->id,
                'is_useful' => true,
                'amount' => 0,
            ]);

        $response
            ->assertOk()
            ->assertExactJson([
                'success' => false,
                'error' => 'Добавлять полезное может только автор челленджа',
            ]);
    }

    public function test_useful_stories_are_separate_from_participant_stories(): void
    {
        $challenge = Challenge::withoutEvents(fn () => Challenge::create([
            'user_id' => auth()->id(),
            'title' => 'Challenge',
            'active' => true,
        ]));

        $challenge->stories()->create([
            'user_id' => auth()->id() + 1,
            'is_useful' => false,
        ]);
        $challenge->usefulStories()->create([
            'user_id' => auth()->id(),
            'is_useful' => true,
        ]);

        $this->assertCount(1, $challenge->stories);
        $this->assertFalse($challenge->stories->first()->is_useful);
        $this->assertCount(1, $challenge->usefulStories);
        $this->assertTrue($challenge->usefulStories->first()->is_useful);
    }
}
