<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Likes;
use App\Models\Media;
use App\Models\Story;
use App\Models\View;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Tests\Feature\Support\CreatesCharacterizationSchema;
use Tests\TestCase;

class StoryGetContractTest extends TestCase
{
    use CreatesCharacterizationSchema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createCharacterizationSchema();
        EloquentModel::unsetEventDispatcher();
    }

    public function test_public_story_get_contract_includes_story_user_and_view_state(): void
    {
        $owner = $this->createCharacterizationUserWithWallets([
            'name' => 'Story Owner',
            'email' => 'story-owner@example.test',
        ]);
        $viewer = $this->createCharacterizationUserWithWallets([
            'name' => 'Story Viewer',
            'email' => 'story-viewer@example.test',
        ]);
        $media = Media::create([
            'user_id' => $owner->id,
            'name' => 'story.mp4',
            'slug' => 'story',
            'slug_ext' => 'story.mp4',
            'mime_type' => 'video/mp4',
            'folder' => 'uploads/stories',
        ]);
        $story = Story::create([
            'user_id' => $owner->id,
            'media_id' => $media->id,
            'description' => 'Contract story',
            'active' => true,
            'declined' => false,
            'broken' => false,
            'paid' => false,
            'amount' => 0,
            'data' => ['source' => 'contract'],
        ]);
        Likes::create([
            'user_id' => $viewer->id,
            'story_id' => $story->id,
            'campaign_id' => 0,
            'ip_address' => '127.0.0.1',
        ]);

        $response = $this
            ->withHeaders(['Accept' => 'application/json'])
            ->getJson('/api/stories/get/' . $story->id . '?user_id=' . $viewer->id);

        $response
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'story_id',
                    'created_at',
                    'views',
                    'paid',
                    'amount',
                    'description',
                    'description_with_tags',
                    'tags',
                    'comments',
                    'comments_count',
                    'path',
                    'likes',
                    'likes_count',
                    'votes',
                    'is_liked',
                    'is_viewed',
                    'is_blocked',
                    'type',
                    'thumbnail',
                    'video_preview',
                    'hls_url',
                    'dash_url',
                    'challenge_id',
                    'challenge' => [
                        'url',
                        'title',
                        'declined',
                    ],
                    'challenge_title',
                    'campaign',
                    'show_story',
                    'story_url',
                    'user',
                    'data',
                ],
            ]);

        $payload = $response->json();

        self::assertTrue($payload['success']);
        self::assertSame($story->id, $payload['data']['story_id']);
        self::assertSame('Contract story', $payload['data']['description']);
        self::assertSame(0, $payload['data']['paid']);
        self::assertSame(0, $payload['data']['amount']);
        self::assertTrue($payload['data']['is_liked']);
        self::assertTrue($payload['data']['is_viewed']);
        self::assertTrue($payload['data']['show_story']);
        self::assertSame(['source' => 'contract'], $payload['data']['data']);
        self::assertIsArray($payload['data']['user']);
        self::assertSame($owner->id, $payload['data']['user']['id']);
        self::assertSame(1, View::query()->where('story_id', $story->id)->where('user_id', $viewer->id)->count());

        $story->update(['description' => 'undefined']);

        $this
            ->withHeaders(['Accept' => 'application/json'])
            ->getJson('/api/stories/get/' . $story->id)
            ->assertOk()
            ->assertJsonPath('data.description_with_tags', '');
    }

    public function test_story_get_missing_story_contract(): void
    {
        $response = $this
            ->withHeaders(['Accept' => 'application/json'])
            ->getJson('/api/stories/get/404404');

        $response
            ->assertOk()
            ->assertJson([
                'success' => false,
                'error' => 'Сторис не найдена',
            ]);
    }
}
