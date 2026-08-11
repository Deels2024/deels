<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Stories;

use App\Models\Battle;
use App\Models\Challenge;
use App\Models\Dislikes;
use App\Models\Likes;
use App\Models\Media;
use App\Models\Story;
use App\Models\User;
use App\Services\Stories\StoryAdminService;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Http\Request;
use Tests\Feature\Support\CreatesCharacterizationSchema;
use Tests\TestCase;

class StoryAdminServiceTest extends TestCase
{
    use CreatesCharacterizationSchema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createCharacterizationSchema();
        EloquentModel::unsetEventDispatcher();
    }

    public function test_stories_list_returns_pending_stories_for_admin(): void
    {
        $admin = $this->createCharacterizationUserWithWallets(['id' => 1, 'user_type' => 'admin']);
        $pending = $this->createStory(10, ['active' => false, 'declined' => false]);
        $this->createStory(11, ['active' => true, 'declined' => false]);

        $data = (new StoryAdminService())->storiesList(Request::create('/admin/stories'), $admin, $admin->id);

        self::assertSame('Модерация сторис', $data['title']);
        self::assertSame([$pending->id], $data['stories']->pluck('id')->all());
    }

    public function test_challenge_stories_list_filters_by_challenge_and_status(): void
    {
        $admin = $this->createCharacterizationUserWithWallets(['id' => 2, 'user_type' => 'admin']);
        $challenge = Challenge::create(['user_id' => 2, 'title' => 'Challenge']);
        $matching = $this->createStory(12, [
            'challenge_id' => $challenge->id,
            'is_main_story' => false,
            'frozen' => true,
            'banned' => false,
        ]);
        $this->createStory(13, [
            'challenge_id' => $challenge->id,
            'is_main_story' => false,
            'frozen' => false,
            'banned' => true,
        ]);

        $request = Request::create('/admin/challenges-stories', 'GET', [
            'challenge_id' => $challenge->id,
            'type' => 'frozen',
        ]);
        $data = (new StoryAdminService())->challengeStoriesList($request, $admin, $admin->id);

        self::assertSame('Модерация ответов на челленджи', $data['title']);
        self::assertSame([$matching->id], $data['stories']->pluck('id')->all());
    }

    public function test_confirm_updates_story_status_and_rejects_foreign_user(): void
    {
        $owner = $this->createCharacterizationUserWithWallets(['id' => 3]);
        $other = $this->createCharacterizationUserWithWallets(['id' => 4]);
        $story = $this->createStory($owner->id, ['active' => false, 'declined' => false]);
        $service = new StoryAdminService();

        self::assertSame(
            ['success' => false],
            $service->confirm(Request::create('/confirm', 'POST', ['story_id' => $story->id, 'action' => 'approve']), $other)
        );

        self::assertSame(
            ['success' => 1],
            $service->confirm(Request::create('/confirm', 'POST', ['story_id' => $story->id, 'action' => 'delete']), $owner)
        );
        self::assertNull(Story::withoutGlobalScopes()->find($story->id));
    }

    public function test_likes_list_returns_liked_stories_for_user(): void
    {
        $user = $this->createCharacterizationUserWithWallets(['id' => 24]);
        $liked = $this->createStory(25);
        $notLiked = $this->createStory(26);
        Likes::create(['user_id' => $user->id, 'story_id' => $liked->id]);
        Likes::create(['user_id' => 999, 'story_id' => $notLiked->id]);

        $data = (new StoryAdminService())->likesList(Request::create('/likes'), $user->id);

        self::assertSame('Лайки', $data['title']);
        self::assertSame([$liked->id], $data['stories']->pluck('id')->all());
        self::assertSame([], $data['campaigns']);
        self::assertSame([], $data['comments']);
    }

    public function test_confirm_challenge_story_toggles_frozen_and_banned_flags(): void
    {
        $admin = $this->createCharacterizationUserWithWallets(['id' => 5, 'user_type' => 'admin']);
        $story = $this->createStory(15, ['challenge_id' => 1, 'is_main_story' => false]);
        $service = new StoryAdminService();

        self::assertSame(
            ['success' => 1],
            $service->confirmChallengeStory(Request::create('/confirm', 'POST', ['story_id' => $story->id, 'action' => 'banned']), $admin)
        );
        $story = $story->fresh();
        self::assertTrue((bool) $story->banned);
        self::assertFalse((bool) $story->frozen);

        $service->confirmChallengeStory(Request::create('/confirm', 'POST', ['story_id' => $story->id, 'action' => 'approved']), $admin);
        $story = $story->fresh();
        self::assertFalse((bool) $story->banned);
        self::assertFalse((bool) $story->frozen);
    }

    public function test_remove_enforces_owner_and_challenge_state_contracts(): void
    {
        $owner = $this->createCharacterizationUserWithWallets(['id' => 6]);
        $other = $this->createCharacterizationUserWithWallets(['id' => 7]);
        $challenge = Challenge::create(['user_id' => $owner->id, 'started' => true]);
        $story = $this->createStory($owner->id, ['challenge_id' => $challenge->id, 'active' => true]);
        $service = new StoryAdminService();

        self::assertSame(
            ['success' => false, 'error' => 'Вы не можете удалить эту сторис'],
            $service->remove(Request::create('/remove', 'POST', ['story_id' => $story->id]), $other)
        );
        self::assertSame(
            ['success' => false, 'error' => 'Вы не можете удалить эту сторис. Челлендж уже запущен.'],
            $service->remove(Request::create('/remove', 'POST', ['story_id' => $story->id]), $owner)
        );

        $challenge->update(['started' => false]);

        self::assertSame(
            ['success' => true, 'message' => 'Сторис удалена'],
            $service->remove(Request::create('/remove', 'POST', ['story_id' => $story->id]), $owner)
        );
    }

    public function test_repost_rejects_invalid_user_and_missing_story(): void
    {
        $user = $this->createCharacterizationUserWithWallets(['id' => 27]);
        $service = new StoryAdminService();

        self::assertSame(
            ['success' => false, 'message' => 'Некорректный запрос'],
            $service->repost(Request::create('/repost', 'POST', ['user_id' => 28]), $user)
        );
        self::assertSame(
            ['success' => false, 'message' => 'Сторис не найдена'],
            $service->repost(Request::create('/repost', 'POST', ['user_id' => $user->id, 'story_id' => 999]), $user)
        );
    }

    public function test_stories_likes_and_dislikes_filter_by_battle(): void
    {
        $battle = Battle::create(['user_id' => 8, 'title' => 'Battle']);
        $matching = $this->createStory(18, ['battle_id' => $battle->id, 'is_main_story' => false]);
        $other = $this->createStory(19, ['is_main_story' => false]);
        Likes::create(['user_id' => 20, 'story_id' => $matching->id]);
        Likes::create(['user_id' => 21, 'story_id' => $other->id]);
        Dislikes::create(['user_id' => 22, 'story_id' => $matching->id]);
        Dislikes::create(['user_id' => 23, 'story_id' => $other->id]);
        $service = new StoryAdminService();
        $request = Request::create('/admin/reactions', 'GET', ['battle' => $battle->id]);

        $likesData = $service->storiesLikes($request);
        $dislikesData = $service->storiesDislikes($request);

        self::assertSame([$matching->id], $likesData['likes']->pluck('story_id')->all());
        self::assertSame([$matching->id], $dislikesData['dislikes']->pluck('story_id')->all());
        self::assertCount(1, $likesData['battles']);
        self::assertCount(1, $dislikesData['battles']);
    }

    public function test_add_likes_inserts_likes_and_views_for_admin_only(): void
    {
        $admin = $this->createCharacterizationUserWithWallets(['id' => 29, 'user_type' => 'admin']);
        $regular = $this->createCharacterizationUserWithWallets(['id' => 30]);
        $story = $this->createStory(31);
        $service = new StoryAdminService();
        $request = Request::create('/admin/add-likes', 'POST', [
            'story_id' => $story->id,
            'likes' => 2,
        ]);

        $service->addLikes($request, $regular);
        self::assertSame(0, Likes::where('story_id', $story->id)->count());

        $service->addLikes($request, $admin);

        self::assertSame(2, Likes::where('story_id', $story->id)->count());
        self::assertGreaterThanOrEqual(2, \App\Models\View::where('story_id', $story->id)->count());
    }

    private function createStory(int $userId, array $attributes = []): Story
    {
        if (!User::find($userId)) {
            $this->createCharacterizationUserWithWallets(['id' => $userId]);
        }
        $media = Media::create([
            'user_id' => $userId,
            'mime_type' => 'image/jpeg',
            'slug' => 'story-admin-' . $userId . '-' . random_int(1000, 9999),
            'slug_ext' => 'story-admin-' . $userId . '.jpg',
            'folder' => 'uploads/stories',
        ]);

        return Story::withoutGlobalScopes()->create(array_merge([
            'user_id' => $userId,
            'media_id' => $media->id,
            'active' => true,
            'declined' => false,
            'broken' => false,
        ], $attributes));
    }
}
