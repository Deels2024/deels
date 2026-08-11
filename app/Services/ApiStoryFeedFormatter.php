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
        $data = [];

        foreach ($media as $mediaItem) {
            [$isLiked, $isViewed] = $this->viewerState($mediaItem->id, $userId);

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

    private function viewerState($storyId, $userId): array
    {
        if (!$userId) {
            return [false, false];
        }

        return [
            Likes::where('story_id', $storyId)->where('user_id', $userId)->exists(),
            View::where('story_id', $storyId)->where('user_id', $userId)->exists(),
        ];
    }
}
