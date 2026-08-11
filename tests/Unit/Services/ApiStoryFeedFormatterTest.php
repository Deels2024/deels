<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Likes;
use App\Models\Story;
use App\Models\View;
use App\Services\ApiAccountInfoService;
use App\Services\ApiStoryFeedFormatter;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\Feature\Support\CreatesCharacterizationSchema;
use Tests\TestCase;

class ApiStoryFeedFormatterTest extends TestCase
{
    use CreatesCharacterizationSchema;

    private ApiStoryFeedFormatter $formatter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createCharacterizationSchema();
        EloquentModel::unsetEventDispatcher();
        $this->formatter = new ApiStoryFeedFormatter(new ApiAccountInfoService());
    }

    public function test_format_adds_viewer_state_user_and_removes_loaded_comments_and_likes(): void
    {
        $owner = $this->createCharacterizationUserWithWallets([
            'name' => 'Feed owner',
            'email' => 'formatter-owner@example.test',
        ]);
        $viewer = $this->createCharacterizationUserWithWallets([
            'name' => 'Feed viewer',
            'email' => 'formatter-viewer@example.test',
        ]);
        $story = Story::create([
            'user_id' => $owner->id,
            'active' => true,
            'declined' => false,
            'broken' => false,
        ]);
        Likes::create([
            'user_id' => $viewer->id,
            'story_id' => $story->id,
        ]);
        View::create([
            'user_id' => $viewer->id,
            'story_id' => $story->id,
        ]);

        $loadedStory = Story::with('comments', 'likes')->find($story->id);

        $data = $this->formatter->format(collect([$loadedStory]), $viewer->id);

        self::assertCount(1, $data);
        self::assertTrue($data[0]->is_liked);
        self::assertTrue($data[0]->is_viewed);
        self::assertIsArray($data[0]->user);
        self::assertSame($owner->id, $data[0]->user['id']);
        self::assertFalse($data[0]->relationLoaded('comments'));
        self::assertFalse($data[0]->relationLoaded('likes'));
    }

    public function test_format_applies_legacy_counters_for_fixed_story_ids(): void
    {
        $owner = $this->createCharacterizationUserWithWallets([
            'name' => 'Legacy owner',
            'email' => 'formatter-legacy@example.test',
        ]);
        $story = Story::create([
            'id' => 211,
            'user_id' => $owner->id,
            'active' => true,
            'declined' => false,
            'broken' => false,
        ]);

        $data = $this->formatter->format(collect([$story]), null);

        self::assertFalse($data[0]->is_liked);
        self::assertFalse($data[0]->is_viewed);
        self::assertSame(10, $data[0]->getAttributes()['comments_count']);
        self::assertSame(5000, $data[0]->getAttributes()['likes_count']);
    }

    public function test_apply_legacy_custom_story_inserts_story_2594_for_legacy_user_without_exclusions(): void
    {
        $owner = $this->createCharacterizationUserWithWallets([
            'name' => 'Legacy feed owner',
            'email' => 'formatter-legacy-feed@example.test',
        ]);
        $story = Story::create([
            'user_id' => $owner->id,
            'active' => true,
            'declined' => false,
            'broken' => false,
        ]);
        Story::create([
            'id' => 2594,
            'user_id' => $owner->id,
            'active' => true,
            'declined' => false,
            'broken' => false,
        ]);
        $media = new LengthAwarePaginator(collect([$story]), 1, 8, 1);

        $this->formatter->applyLegacyCustomStory($media, 67124, null, []);

        self::assertSame([$story->id, 2594], $media->getCollection()->pluck('id')->toArray());
    }

    public function test_apply_legacy_custom_story_skips_when_exclusions_are_present(): void
    {
        $owner = $this->createCharacterizationUserWithWallets([
            'name' => 'Excluded legacy owner',
            'email' => 'formatter-excluded-legacy@example.test',
        ]);
        $story = Story::create([
            'user_id' => $owner->id,
            'active' => true,
            'declined' => false,
            'broken' => false,
        ]);
        Story::create([
            'id' => 2594,
            'user_id' => $owner->id,
            'active' => true,
            'declined' => false,
            'broken' => false,
        ]);
        $media = new LengthAwarePaginator(collect([$story]), 1, 8, 1);

        $this->formatter->applyLegacyCustomStory($media, 67124, null, [$story->id]);

        self::assertSame([$story->id], $media->getCollection()->pluck('id')->toArray());
    }
}
