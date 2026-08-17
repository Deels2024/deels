<?php

declare(strict_types=1);

namespace App\Services\Home;

use App\Models\Battle;
use App\Models\Campaign;
use App\Models\Challenge;
use App\Models\Story;
use Illuminate\Support\Str;

final class HomeMediaResolver
{
    public function story(Story $story, string $aspectRatio = '9:16'): array
    {
        $media = $story->relationLoaded('media') ? $story->getRelation('media') : $story->media;
        $type = $media && Str::contains((string) $media->mime_type, 'video') ? 'video' : 'image';
        $path = $story->path;
        $preview = $media?->video_preview ? url($media->video_preview) : null;
        $poster = $media?->thumbnail ? url($media->thumbnail) : null;

        if ($type === 'image') {
            $poster = $poster ?: $path;
        }

        return [
            'type' => $type,
            'url' => $type === 'video' ? ($preview ?: $path) : $path,
            'poster' => $poster,
            'aspect_ratio' => $aspectRatio,
        ];
    }

    public function contest(Challenge|Battle $contest, string $aspectRatio = '16:9'): array
    {
        $mainStory = $contest->relationLoaded('getMainStory')
            ? $contest->getRelation('getMainStory')
            : null;

        if ($mainStory instanceof Story && $mainStory->media) {
            return $this->story($mainStory, $aspectRatio);
        }

        $media = $contest->relationLoaded('media') ? $contest->getRelation('media') : $contest->media;
        $type = $media && Str::contains((string) $media->mime_type, 'video') ? 'video' : 'image';
        $route = $contest instanceof Battle ? 'battles.get.video' : 'challenges.get.video';
        $path = $type === 'video' && $media ? route($route, $contest->id) : $contest->getFile();

        return [
            'type' => $type,
            'url' => $media?->video_preview ? url($media->video_preview) : $path,
            'poster' => $media?->thumbnail ? url($media->thumbnail) : ($type === 'image' ? $path : null),
            'aspect_ratio' => $aspectRatio,
        ];
    }

    public function campaign(Campaign $campaign): array
    {
        $story = $campaign->relationLoaded('latestActiveStory')
            ? $campaign->getRelation('latestActiveStory')
            : null;

        if ($story instanceof Story && $story->media) {
            return $this->story($story);
        }

        $images = $campaign->feature_img_url();
        $cover = $images->thumbnail ?? $images->feature_image ?? null;

        return [
            'type' => 'image',
            'url' => $cover,
            'poster' => $cover,
            'aspect_ratio' => '9:16',
        ];
    }
}
