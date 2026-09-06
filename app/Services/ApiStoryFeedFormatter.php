<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Likes;
use App\Models\Story;
use App\Models\View;

class ApiStoryFeedFormatter
{
    public function __construct(
        private ApiAccountInfoService $accountInfoService
    ) {
    }

    public function applyLegacyCustomStory($media, $userId, $user, array $excludeIds): void
    {
        if ((!$userId || $userId != 67124) && (!$user || $user->id != 67124)) {
            return;
        }

        if (!empty($excludeIds)) {
            return;
        }

        $customStory = Story::find(2594);
        if (!$customStory) {
            return;
        }

        $result = collect();
        foreach ($media->getCollection() as $item) {
            $result->push($item);
            $result->push($customStory);
        }

        $media->setCollection($result);
    }

    public function format($media, $userId): array
    {
        $this->hydrateViewerState($media, $userId);
        $data = [];

        foreach ($media as $mediaItem) {
            $isLiked = (bool) $mediaItem->getAttribute('is_liked');
            $isViewed = (bool) $mediaItem->getAttribute('is_viewed');

            $mediaItem->campaign = $mediaItem->campaign();
            $mediaItem->user = $this->accountInfoService->build($mediaItem->user_id, true);
            $mediaItem->is_liked = $isLiked;
            $mediaItem->is_viewed = $isViewed;

            if (in_array($mediaItem->id, [211, 212, 213, 214])) {
                $mediaItem->comments_count = 10;
                $mediaItem->likes_count = 5000;
            }

            unset($mediaItem->comments);
            unset($mediaItem->likes);

            $data[] = $mediaItem;
        }

        return $data;
    }

    public function hydrateViewerState($media, $userId): void
    {
        $items = $media instanceof \Illuminate\Contracts\Pagination\Paginator
            ? collect($media->items())
            : collect($media);

        if ($items->isEmpty()) {
            return;
        }

        $alreadyHydrated = $items->every(static function (Story $story): bool {
            $attributes = $story->getAttributes();

            return array_key_exists('is_liked', $attributes)
                && array_key_exists('is_viewed', $attributes);
        });
        if ($alreadyHydrated) {
            return;
        }

        $likedIds = [];
        $viewedIds = [];
        if ($userId) {
            $storyIds = $items->pluck('id')->map(static fn ($id): int => (int) $id)->all();
            $likedIds = Likes::where('user_id', $userId)
                ->whereIn('story_id', $storyIds)
                ->pluck('story_id')
                ->mapWithKeys(static fn ($id): array => [(int) $id => true])
                ->all();
            $viewedIds = View::where('user_id', $userId)
                ->whereIn('story_id', $storyIds)
                ->pluck('story_id')
                ->mapWithKeys(static fn ($id): array => [(int) $id => true])
                ->all();
        }

        $items->each(static function (Story $story) use ($likedIds, $viewedIds): void {
            $storyId = (int) $story->id;
            $story->setAttribute('is_liked', isset($likedIds[$storyId]));
            $story->setAttribute('is_viewed', isset($viewedIds[$storyId]));
        });
    }
}
