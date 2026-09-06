<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Http\Controllers\Api\ChallengeController;
use App\Models\Battle;
use App\Models\Challenge;
use App\Models\Story;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Support\CreatesCharacterizationSchema;
use Tests\TestCase;

class ContestListCharacterizationTest extends TestCase
{
    use CreatesCharacterizationSchema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createCharacterizationSchema();
        EloquentModel::unsetEventDispatcher();
    }

    public function test_challenges_list_returns_paginated_json_contract(): void
    {
        $owner = $this->createCharacterizationUserWithWallets(['name' => 'Owner', 'email' => 'owner@example.test']);
        Challenge::create([
            'user_id' => $owner->id,
            'title' => 'Active challenge',
            'active' => true,
            'declined' => false,
            'finished' => false,
        ]);
        Challenge::create([
            'user_id' => $owner->id,
            'title' => 'Finished challenge',
            'active' => true,
            'declined' => false,
            'finished' => true,
        ]);
        $response = $this
            ->withHeaders(['Accept' => 'application/json'])
            ->getJson('/api/challenges?type=active');

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('current_page', 1)
            ->assertJsonPath('total_pages', 1)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Active challenge');
    }

    public function test_public_challenge_catalog_defaults_to_challenges_without_changing_api_aggregation(): void
    {
        $owner = $this->createCharacterizationUserWithWallets(['name' => 'Owner', 'email' => 'catalog-owner@example.test']);
        $challenge = Challenge::create([
            'user_id' => $owner->id,
            'title' => 'Catalog challenge',
            'active' => true,
            'declined' => false,
            'finished' => false,
        ]);
        Battle::create([
            'user_id' => $owner->id,
            'title' => 'Catalog battle',
            'active' => true,
            'declined' => false,
            'finished' => false,
            'visibility' => 'all',
        ]);
        Story::create([
            'user_id' => $owner->id,
            'challenge_id' => $challenge->id,
            'active' => true,
            'declined' => false,
            'broken' => false,
        ]);

        $request = Request::create('/challenges', 'GET');
        $view = (new ChallengeController())->get_challenges($request);

        self::assertSame('challenges', $request->input('content'));
        self::assertSame(['Catalog challenge'], $view->getData()['challenges']->pluck('title')->all());
        self::assertSame(1, (int) $view->getData()['challenges']->first()->active_stories_count);

        $this
            ->withHeaders(['Accept' => 'application/json'])
            ->getJson('/api/challenges')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_challenges_list_includes_visible_battles(): void
    {
        $owner = $this->createCharacterizationUserWithWallets(['name' => 'Owner', 'email' => 'owner@example.test']);
        Battle::create([
            'user_id' => $owner->id,
            'title' => 'Public battle',
            'active' => true,
            'declined' => false,
            'finished' => false,
            'visibility' => 'all',
        ]);
        Battle::create([
            'user_id' => $owner->id,
            'title' => 'Private battle',
            'active' => true,
            'declined' => false,
            'finished' => false,
            'visibility' => 'participants',
        ]);

        $response = $this
            ->withHeaders(['Accept' => 'application/json'])
            ->getJson('/api/challenges');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Public battle');
    }

    public function test_battles_list_returns_non_paginated_json_contract(): void
    {
        $owner = $this->createCharacterizationUserWithWallets(['name' => 'Owner', 'email' => 'owner@example.test']);
        Battle::create([
            'user_id' => $owner->id,
            'title' => 'Active battle',
            'active' => true,
            'declined' => false,
            'finished' => false,
        ]);
        Battle::create([
            'user_id' => $owner->id,
            'title' => 'Finished battle',
            'active' => true,
            'declined' => false,
            'finished' => true,
        ]);

        $response = $this
            ->withHeaders(['Accept' => 'application/json'])
            ->getJson('/api/battles?type=active');

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonMissingPath('current_page')
            ->assertJsonMissingPath('total_pages')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Active battle');
    }

    public function test_participant_filter_returns_only_contests_with_user_story(): void
    {
        $owner = $this->createCharacterizationUserWithWallets(['name' => 'Owner', 'email' => 'owner@example.test']);
        $participant = $this->createCharacterizationUserWithWallets(['name' => 'Participant', 'email' => 'participant@example.test']);
        $joined = Challenge::create([
            'user_id' => $owner->id,
            'title' => 'Joined challenge',
            'active' => true,
            'declined' => false,
            'finished' => false,
        ]);
        Challenge::create([
            'user_id' => $owner->id,
            'title' => 'Not joined challenge',
            'active' => true,
            'declined' => false,
            'finished' => false,
        ]);
        Story::create([
            'user_id' => $participant->id,
            'challenge_id' => $joined->id,
            'active' => true,
            'declined' => false,
            'broken' => false,
            'is_main_story' => false,
        ]);

        $response = $this
            ->withHeaders(['Accept' => 'application/json'])
            ->getJson('/api/challenges?type=participant&user_id=' . $participant->id);

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Joined challenge');
    }

    public function test_challenges_list_excludes_authors_blocked_by_request_user(): void
    {
        $viewer = $this->createCharacterizationUserWithWallets(['name' => 'Viewer', 'email' => 'viewer@example.test']);
        $blockedOwner = $this->createCharacterizationUserWithWallets(['name' => 'Blocked', 'email' => 'blocked@example.test']);
        $visibleOwner = $this->createCharacterizationUserWithWallets(['name' => 'Visible', 'email' => 'visible@example.test']);

        DB::table('abuses')->insert([
            'user_id' => $blockedOwner->id,
            'abused_by' => $viewer->id,
            'blocked' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Challenge::create([
            'user_id' => $blockedOwner->id,
            'title' => 'Blocked author challenge',
            'active' => true,
            'declined' => false,
            'finished' => false,
        ]);
        Challenge::create([
            'user_id' => $visibleOwner->id,
            'title' => 'Visible author challenge',
            'active' => true,
            'declined' => false,
            'finished' => false,
        ]);

        $response = $this
            ->withHeaders(['Accept' => 'application/json'])
            ->getJson('/api/challenges?user_id=' . $viewer->id);

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Visible author challenge');
    }

    public function test_rewarded_filter_returns_only_challenges_with_a_prize(): void
    {
        $owner = $this->createCharacterizationUserWithWallets(['name' => 'Owner', 'email' => 'owner@example.test']);
        Challenge::create([
            'user_id' => $owner->id,
            'title' => 'Rewarded challenge',
            'active' => true,
            'declined' => false,
            'finished' => false,
            'reward_amount' => 5000,
        ]);
        Challenge::create([
            'user_id' => $owner->id,
            'title' => 'Free challenge',
            'active' => true,
            'declined' => false,
            'finished' => false,
            'reward_amount' => null,
        ]);

        $response = $this
            ->withHeaders(['Accept' => 'application/json'])
            ->getJson('/api/challenges?type=rewarded');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Rewarded challenge');
    }

}
