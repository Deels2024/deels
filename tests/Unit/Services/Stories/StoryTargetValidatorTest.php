<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Stories;

use App\Models\Battle;
use App\Models\Campaign;
use App\Models\Challenge;
use App\Services\Stories\StoryTargetValidator;
use App\Services\Contests\ContestParticipationService;
use Tests\Feature\Support\CreatesCharacterizationSchema;
use Tests\TestCase;

class StoryTargetValidatorTest extends TestCase
{
    use CreatesCharacterizationSchema;

    private StoryTargetValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createCharacterizationSchema();
        $this->validator = new StoryTargetValidator(new ContestParticipationService());
    }

    public function test_validate_challenge_accepts_active_challenge_from_another_user(): void
    {
        $challenge = $this->createChallenge([
            'user_id' => 10,
            'active' => true,
            'finished' => false,
            'declined' => false,
        ]);

        self::assertTrue($this->validator->validateChallenge($challenge->id, 20));
    }

    public function test_validate_challenge_rejects_missing_or_unavailable_challenge(): void
    {
        self::assertFalse($this->validator->validateChallenge(999, 20));

        foreach ([
            ['active' => false, 'finished' => false, 'declined' => false],
            ['active' => true, 'finished' => true, 'declined' => false],
            ['active' => true, 'finished' => false, 'declined' => true],
            ['active' => true, 'finished' => false, 'declined' => false, 'user_id' => 20],
        ] as $attributes) {
            $challenge = $this->createChallenge($attributes + ['user_id' => 10]);

            self::assertFalse($this->validator->validateChallenge($challenge->id, 20));
        }
    }

    public function test_validate_battle_accepts_active_battle_from_another_user(): void
    {
        $battle = $this->createBattle([
            'user_id' => 10,
            'active' => true,
            'finished' => false,
            'declined' => false,
            'called_user_id' => 20,
        ]);

        self::assertTrue($this->validator->validateBattle($battle->id, 20));
    }

    public function test_validate_battle_rejects_missing_or_unavailable_battle(): void
    {
        self::assertFalse($this->validator->validateBattle(999, 20));

        foreach ([
            ['active' => false, 'finished' => false, 'declined' => false],
            ['active' => true, 'finished' => true, 'declined' => false],
            ['active' => true, 'finished' => false, 'declined' => true],
            ['active' => true, 'finished' => false, 'declined' => false, 'user_id' => 20],
            ['active' => true, 'finished' => false, 'declined' => false, 'called_user_id' => 21],
        ] as $attributes) {
            $battle = $this->createBattle($attributes + ['user_id' => 10]);

            self::assertFalse($this->validator->validateBattle($battle->id, 20));
        }
    }

    public function test_validate_campaign_allows_only_owner(): void
    {
        $campaign = Campaign::create([
            'user_id' => 20,
            'title' => 'Owner campaign',
        ]);

        self::assertTrue($this->validator->validateCampaign($campaign->id, 20));
        self::assertFalse($this->validator->validateCampaign($campaign->id, 21));
        self::assertFalse($this->validator->validateCampaign(999, 20));
    }

    private function createChallenge(array $attributes): Challenge
    {
        return Challenge::withoutEvents(fn () => Challenge::create($attributes + [
            'title' => 'Challenge',
        ]));
    }

    private function createBattle(array $attributes): Battle
    {
        return Battle::withoutEvents(fn () => Battle::create($attributes + [
            'title' => 'Battle',
        ]));
    }
}
