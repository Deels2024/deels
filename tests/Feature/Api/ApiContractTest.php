<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Battle;
use App\Models\Challenge;
use App\Models\Likes;
use App\Models\Story;
use App\Models\View;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Tests\Feature\Support\CreatesCharacterizationSchema;
use Tests\TestCase;

class ApiContractTest extends TestCase
{
    use CreatesCharacterizationSchema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createCharacterizationSchema();
        EloquentModel::unsetEventDispatcher();
    }

    public function test_challenges_list_contract_includes_success_data_and_pagination(): void
    {
        $owner = $this->createCharacterizationUserWithWallets([
            'name' => 'Challenge owner',
            'email' => 'challenge-owner@example.test',
        ]);

        Challenge::create([
            'user_id' => $owner->id,
            'title' => 'Contract challenge',
            'active' => true,
            'declined' => false,
            'finished' => false,
        ]);

        $response = $this->withHeaders(['Accept' => 'application/json'])->getJson('/api/challenges?type=active');

        $response
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'user_id',
                        'title',
                        'active',
                        'declined',
                        'finished',
                        'created_at',
                        'updated_at',
                        'user',
                    ],
                ],
                'current_page',
                'total_pages',
            ]);

        $payload = $response->json();

        self::assertTrue($payload['success']);
        self::assertIsArray($payload['data']);
        self::assertIsInt($payload['current_page']);
        self::assertIsInt($payload['total_pages']);
        self::assertIsInt($payload['data'][0]['id']);
        self::assertIsInt($payload['data'][0]['user_id']);
        self::assertIsString($payload['data'][0]['title']);
        self::assertIsArray($payload['data'][0]['user']);
    }

    public function test_battles_list_contract_includes_success_and_data_without_pagination(): void
    {
        $owner = $this->createCharacterizationUserWithWallets([
            'name' => 'Battle owner',
            'email' => 'battle-owner@example.test',
        ]);

        Battle::create([
            'user_id' => $owner->id,
            'title' => 'Contract battle',
            'active' => true,
            'declined' => false,
            'finished' => false,
        ]);

        $response = $this->withHeaders(['Accept' => 'application/json'])->getJson('/api/battles?type=active');

        $response
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'user_id',
                        'title',
                        'active',
                        'declined',
                        'finished',
                        'created_at',
                        'updated_at',
                        'user',
                    ],
                ],
            ])
            ->assertJsonMissingPath('current_page')
            ->assertJsonMissingPath('total_pages');

        $payload = $response->json();

        self::assertTrue($payload['success']);
        self::assertIsArray($payload['data']);
        self::assertIsInt($payload['data'][0]['id']);
        self::assertIsInt($payload['data'][0]['user_id']);
        self::assertIsString($payload['data'][0]['title']);
        self::assertIsArray($payload['data'][0]['user']);
    }

    public function test_challenges_popular_answers_contract_includes_success_data_and_pagination(): void
    {
        $owner = $this->createCharacterizationUserWithWallets([
            'name' => 'Popular owner',
            'email' => 'popular-owner@example.test',
        ]);
        $challenge = Challenge::create([
            'user_id' => $owner->id,
            'title' => 'Popular challenge',
            'active' => true,
            'declined' => false,
            'finished' => false,
        ]);
        Story::create([
            'user_id' => $owner->id,
            'challenge_id' => $challenge->id,
            'active' => true,
            'declined' => false,
            'broken' => false,
            'is_main_story' => false,
        ]);

        $response = $this->withHeaders(['Accept' => 'application/json'])->getJson('/api/challenges/popular_answers');

        $response
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'user_id',
                        'challenge_id',
                        'active',
                        'declined',
                        'is_liked',
                        'is_viewed',
                        'comments_count',
                        'likes_count',
                        'views_count',
                        'user',
                    ],
                ],
                'current_page',
                'total_pages',
            ]);

        $payload = $response->json();

        self::assertTrue($payload['success']);
        self::assertIsArray($payload['data']);
        self::assertIsInt($payload['current_page']);
        self::assertIsInt($payload['total_pages']);
        self::assertIsBool($payload['data'][0]['is_liked']);
        self::assertIsBool($payload['data'][0]['is_viewed']);
    }

    public function test_story_store_error_contract_includes_success_false_and_error_string(): void
    {
        $this->actingAs($this->createCharacterizationUserWithWallets([
            'name' => 'Story user',
            'email' => 'story-contract@example.test',
        ]), 'sanctum');

        $response = $this
            ->withHeaders(['Accept' => 'application/json'])
            ->postJson('/api/stories/store', ['amount' => 0]);

        $response
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'error',
            ])
            ->assertJsonMissingPath('data')
            ->assertJsonMissingPath('errors');

        $payload = $response->json();

        self::assertFalse($payload['success']);
        self::assertIsString($payload['error']);
    }

    public function test_stories_list_contract_includes_feed_fields_html_and_pagination(): void
    {
        $owner = $this->createCharacterizationUserWithWallets([
            'name' => 'Feed owner',
            'email' => 'feed-owner@example.test',
        ]);
        $viewer = $this->createCharacterizationUserWithWallets([
            'name' => 'Feed viewer',
            'email' => 'feed-viewer@example.test',
        ]);
        $story = Story::create([
            'user_id' => $owner->id,
            'active' => true,
            'declined' => false,
            'broken' => false,
            'is_main_story' => false,
        ]);
        Likes::create([
            'user_id' => $viewer->id,
            'story_id' => $story->id,
        ]);
        View::create([
            'user_id' => $viewer->id,
            'story_id' => $story->id,
        ]);

        $response = $this
            ->withSession(['session_rand' => 123])
            ->withHeaders(['Accept' => 'application/json'])
            ->getJson('/api/stories_list?type=new&user_id=' . $viewer->id);

        $response
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'user_id',
                        'active',
                        'declined',
                        'is_liked',
                        'is_viewed',
                        'comments_count',
                        'likes_count',
                        'user',
                    ],
                ],
                'html',
                'current_page',
                'total_pages',
                'has_more',
            ]);

        $payload = $response->json();

        self::assertTrue($payload['success']);
        self::assertIsArray($payload['data']);
        self::assertIsString($payload['html']);
        self::assertIsInt($payload['current_page']);
        self::assertIsInt($payload['total_pages']);
        self::assertIsBool($payload['has_more']);
        self::assertIsBool($payload['data'][0]['is_liked']);
        self::assertIsBool($payload['data'][0]['is_viewed']);
        self::assertTrue($payload['data'][0]['is_liked']);
        self::assertTrue($payload['data'][0]['is_viewed']);
        self::assertIsArray($payload['data'][0]['user']);
    }
}
