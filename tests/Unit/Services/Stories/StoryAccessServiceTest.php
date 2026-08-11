<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Stories;

use App\Models\Likes;
use App\Models\Media;
use App\Models\Story;
use App\Models\View;
use App\Services\Stories\StoryAccessService;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Tests\Feature\Support\CreatesCharacterizationSchema;
use Tests\TestCase;

class StoryAccessServiceTest extends TestCase
{
    use CreatesCharacterizationSchema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createCharacterizationSchema();
        EloquentModel::unsetEventDispatcher();
    }

    public function test_resolve_marks_free_story_as_visible_viewed_and_liked(): void
    {
        $owner = $this->createCharacterizationUserWithWallets(['id' => 10]);
        $viewer = $this->createCharacterizationUserWithWallets(['id' => 20]);
        $story = $this->createStory($owner->id, false);
        Likes::create([
            'user_id' => $viewer->id,
            'story_id' => $story->id,
        ]);

        $result = (new StoryAccessService())->resolve($story, $viewer, $viewer->id, $story->id, false, false);

        self::assertSame([
            'is_liked' => true,
            'is_viewed' => true,
            'show_story' => true,
            'blocked' => false,
        ], $result);
        self::assertDatabaseHas('views', [
            'user_id' => $viewer->id,
            'story_id' => $story->id,
        ]);
    }

    public function test_resolve_hides_paid_story_until_it_has_existing_view(): void
    {
        $owner = $this->createCharacterizationUserWithWallets(['id' => 11]);
        $viewer = $this->createCharacterizationUserWithWallets(['id' => 21]);
        $story = $this->createStory($owner->id, true);

        $hidden = (new StoryAccessService())->resolve($story, $viewer, $viewer->id, $story->id, false, false);

        self::assertFalse($hidden['show_story']);
        self::assertFalse($hidden['is_viewed']);

        View::create([
            'user_id' => $viewer->id,
            'story_id' => $story->id,
        ]);

        $visible = (new StoryAccessService())->resolve($story, $viewer, $viewer->id, $story->id, false, false);

        self::assertTrue($visible['show_story']);
        self::assertTrue($visible['is_viewed']);
    }

    private function createStory(int $ownerId, bool $paid): Story
    {
        $media = Media::create([
            'user_id' => $ownerId,
            'mime_type' => 'image/jpeg',
            'slug' => 'story-' . $ownerId . '-' . (int) $paid,
            'slug_ext' => 'story-' . $ownerId . '-' . (int) $paid . '.jpg',
            'folder' => 'uploads/stories',
        ]);

        return Story::create([
            'user_id' => $ownerId,
            'media_id' => $media->id,
            'paid' => $paid,
            'amount' => $paid ? 50 : 0,
            'active' => true,
            'declined' => false,
            'broken' => false,
        ]);
    }
}
