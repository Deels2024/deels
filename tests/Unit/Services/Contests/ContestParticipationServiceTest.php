<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Contests;

use App\Models\Battle;
use App\Models\Challenge;
use App\Models\Story;
use App\Services\Contests\ContestParticipationService;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Support\CreatesCharacterizationSchema;
use Tests\TestCase;

class ContestParticipationServiceTest extends TestCase
{
    use CreatesCharacterizationSchema;

    private ContestParticipationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createCharacterizationSchema();
        $this->service = new ContestParticipationService();
    }

    public function test_challenge_result_is_hidden_on_leave_and_restored_on_rejoin(): void
    {
        $challenge = Challenge::withoutEvents(fn () => Challenge::create([
            'user_id' => 10,
            'title' => 'Challenge',
            'active' => true,
            'participants_count' => 3,
        ]));
        $story = Story::withoutEvents(fn () => Story::create([
            'user_id' => 20,
            'challenge_id' => $challenge->id,
            'active' => true,
        ]));

        self::assertSame('leave', $this->service->state($challenge, 'challenge', 20)['action']);

        $this->service->leave($challenge, 'challenge', 20);
        self::assertNotNull($story->fresh()->withdrawn_at);
        self::assertSame(0, $challenge->stories()->active()->count());
        self::assertSame('rejoin', $this->service->state($challenge, 'challenge', 20)['action']);

        $this->service->rejoin($challenge, 'challenge', 20);
        self::assertNull($story->fresh()->withdrawn_at);
        self::assertSame(1, $challenge->stories()->active()->count());
    }

    public function test_single_participant_can_leave_and_rejoin_before_challenge_starts(): void
    {
        $challenge = Challenge::withoutEvents(fn () => Challenge::create([
            'user_id' => 10,
            'title' => 'Single challenge',
            'active' => true,
            'started' => false,
            'participants_count' => 1,
        ]));

        $this->service->join($challenge, 'challenge', 20);
        self::assertFalse($this->service->state($challenge, 'challenge', 20)['singleAuthor']);

        $this->service->leave($challenge, 'challenge', 20);

        self::assertFalse((bool) $challenge->fresh()->finished);
        self::assertSame('join', $this->service->state($challenge, 'challenge', 20)['action']);

        $this->service->join($challenge, 'challenge', 20);
        self::assertTrue($this->service->state($challenge, 'challenge', 20)['participating']);
    }

    public function test_single_participant_exit_finishes_started_challenge(): void
    {
        $challenge = Challenge::withoutEvents(fn () => Challenge::create([
            'user_id' => 10,
            'title' => 'Started single challenge',
            'active' => true,
            'started' => true,
            'participants_count' => 1,
        ]));

        $this->service->join($challenge, 'challenge', 20);
        self::assertTrue($this->service->state($challenge, 'challenge', 20)['singleAuthor']);

        $this->service->leave($challenge, 'challenge', 20);

        self::assertTrue((bool) $challenge->fresh()->finished);
    }

    public function test_invitations_do_not_restrict_challenge_participation(): void
    {
        $challenge = Challenge::withoutEvents(fn () => Challenge::create([
            'user_id' => 10,
            'title' => 'Challenge with invitations',
            'active' => true,
            'participants_count' => 2,
            'invite_user_ids' => [20],
        ]));

        self::assertSame('join', $this->service->state($challenge, 'challenge', 21)['action']);

        $this->service->join($challenge, 'challenge', 21);

        self::assertTrue($this->service->state($challenge, 'challenge', 21)['participating']);
    }

    public function test_battle_exit_marks_user_as_loser_and_finishes_battle(): void
    {
        $battle = $this->battle();
        Story::withoutEvents(fn () => Story::create([
            'user_id' => 20,
            'battle_id' => $battle->id,
            'active' => true,
        ]));

        $this->service->leave($battle, 'battle', 20);

        $battle->refresh();
        self::assertTrue((bool) $battle->finished);
        self::assertSame(20, $battle->loser_user_id);
    }

    public function test_battle_owner_can_leave_without_a_result_story(): void
    {
        $battle = $this->battle();

        self::assertSame('leave', $this->service->state($battle, 'battle', 10)['action']);

        $this->service->leave($battle, 'battle', 10);

        $battle->refresh();
        self::assertTrue((bool) $battle->finished);
        self::assertSame(10, $battle->loser_user_id);
    }

    public function test_only_called_user_can_join_and_can_decline(): void
    {
        $battle = $this->battle();

        self::assertSame('accept', $this->service->state($battle, 'battle', 20)['action']);
        self::assertTrue($this->service->state($battle, 'battle', 20)['called']);
        self::assertSame('disabled', $this->service->state($battle, 'battle', 21)['action']);

        $this->service->decline($battle, 20);

        $battle->refresh();
        self::assertFalse((bool) $battle->finished);
        self::assertNull($battle->called_user_id);
        self::assertSame('declined', DB::table('contest_participations')->value('status'));
    }

    private function battle(): Battle
    {
        return Battle::withoutEvents(fn () => Battle::create([
            'user_id' => 10,
            'called_user_id' => 20,
            'title' => 'Battle',
            'active' => true,
            'participants_count' => 2,
        ]));
    }
}
