<?php

declare(strict_types=1);

namespace App\Services\Stories;

use App\Models\Story;
use Carbon\Carbon;

class StoryViewFormatter
{
    public function format(
        Story $story,
        array $userData,
        ?int $userId,
        bool $isLiked,
        bool $isViewed,
        bool $blocked,
        bool $showStory
    ): array {
        $storyComments = $this->formatComments($story, $userId);
        $storyTags = $story->tags ? $story->tags()->pluck('title')->toArray() : [];
        $storyDescription = $this->descriptionWithTags($story, $storyTags);

        return [
            'story_id' => $story->id,
            'created_at' => ' \ ' . Carbon::parse($story->created_at)->format('d.m.Y H:i'),
            'views' => $story->views()->count(),
            'paid' => $story->paid,
            'amount' => $story->amount,
            'description' => $story->description,
            'description_with_tags' => $storyDescription,
            'tags' => $storyTags,
            'comments' => $storyComments,
            'comments_count' => $story->comments_count,
            'path' => $story->media ? route('stories.get.video', $story->id) : $story->getFile(),
            'likes' => $story->likes,
            'likes_count' => $story->likes_count,
            'votes' => $story->votes,
            'is_liked' => $isLiked,
            'is_viewed' => $isViewed,
            'is_blocked' => $blocked,
            'type' => $story->type,
            'thumbnail' => $story->thumbnail,
            'video_preview' => $story->video_preview,
            'hls_url' => $story->hls_url,
            'dash_url' => $story->media->dash_url ?? null,
            'challenge_id' => $story->challenge_id,
            'challenge' => [
                'url' => $story->challenge ? route('challenge_page', $story->challenge_id) : '',
                'title' => $story->challenge ? $story->challenge->title : '',
                'declined' => $story->challenge ? $story->challenge->declined : '',
            ],
            'challenge_title' => $story->challenge_id ? route('challenge_page', $story->challenge_id) : '',
            'campaign' => $story->campaign,
            'show_story' => $showStory,
            'story_url' => $story->getStoryShareUrl() ?? null,
            'user' => $userData,
            'data' => $story->data ?? null,
        ];
    }

    private function formatComments(Story $story, ?int $userId): mixed
    {
        $storyComments = $story->comments;
        if (count($storyComments) === 0) {
            return $storyComments;
        }

        $formatted = [];
        foreach ($storyComments as $storyComment) {
            $storyComment->is_liked = $storyComment->isLiked($userId);
            $formatted[] = $storyComment;
        }

        return $formatted;
    }

    private function descriptionWithTags(Story $story, array $storyTags): string
    {
        $storyDescription = trim((string) $story->description);

        // FormData serializes a missing JavaScript value as the literal string
        // "undefined". Do not expose that implementation detail in the story UI.
        if (strcasecmp($storyDescription, 'undefined') === 0) {
            $storyDescription = '';
        }

        if (!empty($storyTags)) {
            $storyDescription .= '<br>#' . implode(' #', $storyTags);
        }

        return $storyDescription;
    }
}
