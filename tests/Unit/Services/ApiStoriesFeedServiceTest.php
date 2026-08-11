<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Abuse;
use App\Models\Story;
use App\Services\ApiStoriesFeedService;
use App\Services\RecommendationService;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Http\Request;
use Mockery;
use Tests\Feature\Support\CreatesCharacterizationSchema;
use Tests\TestCase;

class ApiStoriesFeedServiceTest extends TestCase
{
    use CreatesCharacterizationSchema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createCharacterizationSchema();
        EloquentModel::unsetEventDispatcher();
    }

    public function test_type_new_returns_newest_stories_first(): void
    {
        $owner = $this->createCharacterizationUserWithWallets([
            'name' => 'Newest owner',
            'email' => 'newest-owner@example.test',
        ]);
        $old = $this->createStory([
            'user_id' => $owner->id,
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);
        $new = $this->createStory([
            'user_id' => $owner->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $result = $this->service()->build($this->request(['type' => 'new']));

        self::assertSame([$new->id, $old->id], $result->media->getCollection()->pluck('id')->toArray());
    }

    public function test_type_paid_returns_only_paid_stories_with_positive_amount(): void
    {
        $owner = $this->createCharacterizationUserWithWallets([
            'name' => 'Paid owner',
            'email' => 'paid-owner@example.test',
        ]);
        $paid = $this->createStory([
            'user_id' => $owner->id,
            'paid' => true,
            'amount' => 100,
        ]);
        $this->createStory([
            'user_id' => $owner->id,
            'paid' => true,
            'amount' => 0,
        ]);
        $this->createStory([
            'user_id' => $owner->id,
            'paid' => false,
            'amount' => 100,
        ]);

        $result = $this->service()->build($this->request(['type' => 'paid']));

        self::assertSame([$paid->id], $result->media->getCollection()->pluck('id')->toArray());
    }

    public function test_exclude_ids_string_excludes_matching_stories_and_keeps_requested_page(): void
    {
        $owner = $this->createCharacterizationUserWithWallets([
            'name' => 'Exclude string owner',
            'email' => 'exclude-string-owner@example.test',
        ]);
        $excluded = $this->createStory(['user_id' => $owner->id]);
        $kept = $this->createStory(['user_id' => $owner->id]);

        $result = $this->service()->build($this->request([
            'type' => 'new',
            'exclude_ids' => (string) $excluded->id,
            'page' => 3,
        ]));

        self::assertSame([$kept->id], $result->media->getCollection()->pluck('id')->toArray());
        self::assertSame([$excluded->id], array_values($result->excludeIds));
        self::assertSame(3, $result->requestedPage);
        self::assertSame(1, $result->media->currentPage());
    }

    public function test_exclude_ids_array_excludes_matching_stories(): void
    {
        $owner = $this->createCharacterizationUserWithWallets([
            'name' => 'Exclude array owner',
            'email' => 'exclude-array-owner@example.test',
        ]);
        $excluded = $this->createStory(['user_id' => $owner->id]);
        $kept = $this->createStory(['user_id' => $owner->id]);

        $result = $this->service()->build($this->request([
            'type' => 'new',
            'exclude_ids' => [$excluded->id],
        ]));

        self::assertSame([$kept->id], $result->media->getCollection()->pluck('id')->toArray());
        self::assertSame([$excluded->id], array_values($result->excludeIds));
    }

    public function test_blocked_author_is_excluded_for_request_user(): void
    {
        $viewer = $this->createCharacterizationUserWithWallets([
            'name' => 'Blocked viewer',
            'email' => 'blocked-viewer@example.test',
        ]);
        $blockedAuthor = $this->createCharacterizationUserWithWallets([
            'name' => 'Blocked author',
            'email' => 'blocked-author@example.test',
        ]);
        $visibleAuthor = $this->createCharacterizationUserWithWallets([
            'name' => 'Visible author',
            'email' => 'visible-author@example.test',
        ]);
        $blockedStory = $this->createStory(['user_id' => $blockedAuthor->id]);
        $visibleStory = $this->createStory(['user_id' => $visibleAuthor->id]);
        Abuse::create([
            'user_id' => $blockedAuthor->id,
            'abused_by' => $viewer->id,
            'blocked' => true,
        ]);

        $result = $this->service()->build($this->request([
            'type' => 'new',
            'user_id' => $viewer->id,
        ]));

        self::assertSame([$visibleStory->id], $result->media->getCollection()->pluck('id')->toArray());
        self::assertSame($viewer->id, $result->user->id);
        self::assertNotContains($blockedStory->id, $result->media->getCollection()->pluck('id')->toArray());
    }

    private function service(): ApiStoriesFeedService
    {
        $recommendations = Mockery::mock(RecommendationService::class);
        $recommendations
            ->shouldReceive('getRecommendationsForUser')
            ->zeroOrMoreTimes()
            ->andReturn([]);

        return new ApiStoriesFeedService($recommendations);
    }

    private function request(array $query): Request
    {
        $request = Request::create('/api/stories_list', 'GET', $query);
        $request->setLaravelSession($this->app['session.store']);

        return $request;
    }

    private function createStory(array $attributes): Story
    {
        return Story::create($attributes + [
            'active' => true,
            'declined' => false,
            'broken' => false,
            'is_main_story' => false,
        ]);
    }
}
