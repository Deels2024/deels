<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Contests;

use App\Models\Challenge;
use App\Models\User;
use App\Services\Contests\ContestVisibilityService;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Support\CreatesCharacterizationSchema;
use Tests\TestCase;

class ContestVisibilityServiceTest extends TestCase
{
    use CreatesCharacterizationSchema;

    private ContestVisibilityService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createCharacterizationSchema();
        $this->service = new ContestVisibilityService();
    }

    public function test_participants_only_catalog_matches_direct_visibility_check(): void
    {
        $author = User::withoutEvents(fn () => User::create(['name' => 'Author']));
        $participant = User::withoutEvents(fn () => User::create(['name' => 'Participant']));
        $outsider = User::withoutEvents(fn () => User::create(['name' => 'Outsider']));
        $challenge = Challenge::withoutEvents(fn () => Challenge::create([
            'user_id' => $author->id,
            'title' => 'Private challenge',
            'active' => true,
            'visibility' => ContestVisibilityService::PARTICIPANTS,
        ]));

        DB::table('contest_participations')->insert([
            'contest_type' => 'challenge',
            'contest_id' => $challenge->id,
            'user_id' => $participant->id,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        self::assertTrue($this->service->canView($challenge, $participant));
        self::assertFalse($this->service->canView($challenge, $outsider));
        self::assertTrue($this->catalogContains($challenge, $participant));
        self::assertFalse($this->catalogContains($challenge, $outsider));
        self::assertFalse($this->catalogContains($challenge, null));
    }

    public function test_visibility_filter_accepts_user_challenges_relation(): void
    {
        $author = User::withoutEvents(fn () => User::create(['name' => 'Author']));
        $challenge = Challenge::withoutEvents(fn () => Challenge::create([
            'user_id' => $author->id,
            'title' => 'Author challenge',
            'active' => true,
            'visibility' => ContestVisibilityService::ALL,
        ]));

        $query = $this->service->applyToContests($author->challenges(), 'challenges', $author);

        self::assertTrue($query->where('challenges.id', $challenge->id)->exists());
    }

    private function catalogContains(Challenge $challenge, ?User $viewer): bool
    {
        return $this->service
            ->applyToContests(Challenge::query(), 'challenges', $viewer)
            ->where('challenges.id', $challenge->id)
            ->exists();
    }
}
