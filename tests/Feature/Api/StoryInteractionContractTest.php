<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Battle;
use App\Models\Challenge;
use App\Models\Comment;
use App\Models\Dislikes;
use App\Models\Likes;
use App\Models\Story;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Laravel\Sanctum\Sanctum;
use Tests\Feature\Support\CreatesCharacterizationSchema;
use Tests\TestCase;

class StoryInteractionContractTest extends TestCase
{
    use CreatesCharacterizationSchema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createCharacterizationSchema();
        EloquentModel::unsetEventDispatcher();
    }

    public function test_like_adds_like_and_returns_contract(): void
    {
        $user = $this->createCharacterizationUserWithWallets(['email' => 'like-user@example.test']);
        $story = $this->createStory();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/stories/like', [
            'story_id' => $story->id,
            'user_id' => $user->id,
        ]);

        $response
            ->assertOk()
            ->assertJsonStructure(['success', 'votes', 'votes_data', 'message'])
            ->assertJson([
                'success' => true,
                'message' => 'Лайк добавлен',
            ]);

        self::assertSame(1, Likes::where('story_id', $story->id)->where('user_id', $user->id)->count());
    }

    public function test_like_rejects_duplicate_ip_contract(): void
    {
        $user = $this->createCharacterizationUserWithWallets(['email' => 'like-duplicate@example.test']);
        $story = $this->createStory();
        Likes::create([
            'story_id' => $story->id,
            'user_id' => 999,
            'campaign_id' => 0,
            'ip_address' => '127.0.0.1',
        ]);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/stories/like', [
            'story_id' => $story->id,
            'user_id' => $user->id,
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'success' => false,
                'errors' => 'Лайк уже добавлен с вашего IP-адреса',
            ]);
    }

    public function test_dislike_requires_battle_contract(): void
    {
        $user = $this->createCharacterizationUserWithWallets(['email' => 'dislike-user@example.test']);
        $story = $this->createStory();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/stories/dislike', [
            'story_id' => $story->id,
            'user_id' => $user->id,
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'success' => false,
                'errors' => 'Батл не найден. Вы не можете поставить дизлайк.',
            ]);
    }

    public function test_dislike_adds_dislike_for_battle_story_contract(): void
    {
        $user = $this->createCharacterizationUserWithWallets(['email' => 'battle-dislike@example.test']);
        $battle = Battle::create([
            'user_id' => 777,
            'title' => 'Battle',
            'active' => true,
            'declined' => false,
            'finished' => false,
        ]);
        $story = $this->createStory(['battle_id' => $battle->id]);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/stories/dislike', [
            'story_id' => $story->id,
            'user_id' => $user->id,
        ]);

        $response
            ->assertOk()
            ->assertJsonStructure(['success', 'votes', 'votes_data', 'message'])
            ->assertJson([
                'success' => true,
                'message' => 'Дизлайк добавлен',
            ]);

        self::assertSame(1, Dislikes::where('story_id', $story->id)->where('user_id', $user->id)->count());
    }

    public function test_comment_like_adds_like_contract(): void
    {
        $user = $this->createCharacterizationUserWithWallets(['email' => 'comment-like@example.test']);
        $story = $this->createStory();
        $comment = Comment::create([
            'story_id' => $story->id,
            'user_id' => $user->id,
            'comment' => 'Existing comment',
            'approved' => true,
        ]);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/stories/comment/like', [
            'story_id' => $story->id,
            'comment_id' => $comment->id,
            'user_id' => $user->id,
        ]);

        $response
            ->assertOk()
            ->assertJsonStructure(['success', 'message', 'count'])
            ->assertJson([
                'success' => true,
                'message' => 'Лайк добавлен',
                'count' => 1,
            ]);

        self::assertSame(1, Likes::where('comment_id', $comment->id)->where('user_id', $user->id)->count());
    }

    public function test_comment_creates_comment_contract(): void
    {
        $user = $this->createCharacterizationUserWithWallets([
            'name' => 'Commenter',
            'email' => 'commenter@example.test',
        ]);
        $story = $this->createStory();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/stories/comment', [
            'story_id' => $story->id,
            'user_id' => $user->id,
            'comment' => 'Contract comment',
        ]);

        $response
            ->assertOk()
            ->assertJsonStructure(['success', 'comment_id', 'message'])
            ->assertJson([
                'success' => true,
                'message' => 'Комментарий опубликован',
            ]);

        self::assertSame(1, Comment::where('story_id', $story->id)->where('comment', 'Contract comment')->count());
    }

    public function test_like_rejects_frozen_challenge_contract(): void
    {
        $user = $this->createCharacterizationUserWithWallets(['email' => 'frozen-like@example.test']);
        $challenge = Challenge::create([
            'user_id' => 777,
            'title' => 'Frozen',
            'active' => true,
            'declined' => false,
            'finished' => false,
            'frozen' => true,
        ]);
        $story = $this->createStory(['challenge_id' => $challenge->id]);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/stories/like', [
            'story_id' => $story->id,
            'user_id' => $user->id,
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'success' => false,
                'errors' => 'Челлендж заморожен. Вы не можете поставить лайк.',
            ]);
    }

    private function createStory(array $attributes = []): Story
    {
        $owner = $this->createCharacterizationUserWithWallets([
            'email' => 'owner-' . uniqid('', true) . '@example.test',
        ]);

        return Story::withoutGlobalScopes()->create($attributes + [
            'user_id' => $owner->id,
            'description' => 'Story',
            'active' => true,
            'declined' => false,
            'broken' => false,
            'paid' => false,
            'amount' => 0,
        ]);
    }
}
