<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Stories;

use App\Models\Story;
use App\Services\Stories\StoryReplacementService;
use Tests\Feature\Support\CreatesCharacterizationSchema;
use Tests\TestCase;

class StoryReplacementServiceTest extends TestCase
{
    use CreatesCharacterizationSchema;

    private StoryReplacementService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createCharacterizationSchema();
        $this->service = new StoryReplacementService();
    }

    public function test_has_challenge_story_ignores_main_stories(): void
    {
        Story::create([
            'user_id' => 10,
            'challenge_id' => 100,
            'is_main_story' => true,
        ]);

        self::assertFalse($this->service->hasChallengeStory(100, 10));

        Story::create([
            'user_id' => 10,
            'challenge_id' => 100,
            'is_main_story' => false,
        ]);

        self::assertTrue($this->service->hasChallengeStory(100, 10));
    }

    public function test_has_battle_story_uses_battle_id_and_ignores_challenge_id(): void
    {
        Story::create([
            'user_id' => 20,
            'challenge_id' => 200,
            'is_main_story' => false,
        ]);

        self::assertFalse($this->service->hasBattleStory(200, 20));

        Story::create([
            'user_id' => 20,
            'battle_id' => 200,
            'is_main_story' => false,
        ]);

        self::assertTrue($this->service->hasBattleStory(200, 20));
    }

    public function test_delete_challenge_story_removes_only_non_main_matching_story(): void
    {
        $deleted = Story::create([
            'user_id' => 30,
            'challenge_id' => 300,
            'is_main_story' => false,
        ]);
        $keptMain = Story::create([
            'user_id' => 30,
            'challenge_id' => 300,
            'is_main_story' => true,
        ]);
        $keptOtherUser = Story::create([
            'user_id' => 31,
            'challenge_id' => 300,
            'is_main_story' => false,
        ]);

        self::assertFalse($this->service->deleteChallengeStory(300, 30));

        self::assertNull(Story::withoutGlobalScopes()->find($deleted->id));
        self::assertNotNull(Story::withoutGlobalScopes()->find($keptMain->id));
        self::assertNotNull(Story::withoutGlobalScopes()->find($keptOtherUser->id));
    }

    public function test_delete_battle_story_removes_only_non_main_matching_story(): void
    {
        $deleted = Story::create([
            'user_id' => 40,
            'battle_id' => 400,
            'is_main_story' => null,
        ]);
        $keptMain = Story::create([
            'user_id' => 40,
            'battle_id' => 400,
            'is_main_story' => true,
        ]);
        $keptOtherBattle = Story::create([
            'user_id' => 40,
            'battle_id' => 401,
            'is_main_story' => false,
        ]);

        self::assertFalse($this->service->deleteBattleStory(400, 40));

        self::assertNull(Story::withoutGlobalScopes()->find($deleted->id));
        self::assertNotNull(Story::withoutGlobalScopes()->find($keptMain->id));
        self::assertNotNull(Story::withoutGlobalScopes()->find($keptOtherBattle->id));
    }
}
