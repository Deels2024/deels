<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Battle;
use App\Models\Challenge;
use App\Models\Media;
use App\Models\Story;
use Tests\TestCase;

class MediaAccessorsCharacterizationTest extends TestCase
{
    public function test_story_media_accessors_for_image(): void
    {
        $story = new Story(['id' => 123]);
        $story->setRelation('media', new Media([
            'slug' => 'photo',
            'slug_ext' => 'photo.jpg',
            'mime_type' => 'image/jpeg',
            'folder' => 'uploads/stories/custom',
        ]));

        self::assertSame('image', $story->type);
        self::assertSame(url('uploads/stories/custom/photo.jpg'), $story->filepath);
        self::assertSame(route('stories.get.video', 123), $story->path);
    }

    public function test_challenge_media_accessors_for_video(): void
    {
        $challenge = new Challenge(['id' => 10]);
        $challenge->setRelation('media', new Media([
            'slug' => 'clip',
            'slug_ext' => 'clip.mp4',
            'mime_type' => 'video/mp4',
            'folder' => 'uploads/challenges/custom',
        ]));

        self::assertSame('video', $challenge->type);
        self::assertSame(url('uploads/challenges/custom/clip.mp4'), $challenge->filepath);
        self::assertSame(route('challenges.get.video', 10), $challenge->path);
    }

    public function test_battle_media_accessors_for_image(): void
    {
        $battle = new Battle(['id' => 20]);
        $battle->setRelation('media', new Media([
            'slug' => 'cover',
            'slug_ext' => 'cover.png',
            'mime_type' => 'image/png',
            'folder' => 'uploads/battles/custom',
        ]));

        self::assertSame('image', $battle->type);
        self::assertSame(url('uploads/battles/custom/cover.png'), $battle->filepath);
        self::assertSame(url('uploads/battles/custom/cover.png'), $battle->path);
    }
}
