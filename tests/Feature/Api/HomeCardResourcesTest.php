<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Http\Resources\Home\BattleCardResource;
use App\Http\Resources\Home\CampaignCardResource;
use App\Http\Resources\Home\ChallengeCardResource;
use App\Models\Battle;
use App\Models\Campaign;
use App\Models\Challenge;
use App\Models\Media;
use App\Models\Story;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;

class HomeCardResourcesTest extends TestCase
{
    public function test_challenge_card_uses_vertical_media_contract_and_preloaded_counts(): void
    {
        $challenge = new Challenge([
            'id' => 10,
            'title' => 'Dance challenge',
            'reward_amount' => 50000,
            'participants_count' => 20,
            'min_participants' => 2,
            'finished' => false,
            'started' => true,
            'finish' => now()->addDays(5),
        ]);
        $challenge->setAttribute('stories_count', 7);
        $challenge->setAttribute('views_count', 120);
        $challenge->setRelation('user', $this->user(1, 'creator'));
        $challenge->setRelation('media', $this->media(1, 'image/jpeg', 'challenge.jpg'));
        $challenge->setRelation('getMainStory', null);

        $data = (new ChallengeCardResource($challenge))->resolve(request());

        $this->assertSame('9:16', $data['media']['aspect_ratio']);
        $this->assertSame(50000, $data['reward_amount']);
        $this->assertSame(7, $data['participants']['current']);
        $this->assertSame(120, $data['stats']['views']);
    }

    public function test_battle_card_contains_two_vertical_opponents(): void
    {
        $creator = $this->user(1, 'creator');
        $opponent = $this->user(2, 'opponent');
        $creatorStory = $this->story(11, $creator, $this->media(11, 'video/mp4', 'creator.mp4'));
        $opponentStory = $this->story(12, $opponent, $this->media(12, 'video/mp4', 'opponent.mp4'));

        $battle = new Battle([
            'id' => 20,
            'title' => 'Dance battle',
            'user_id' => 1,
            'called_user_id' => 2,
            'reward_amount' => 30000,
            'finish' => now()->addDays(3),
        ]);
        $battle->setAttribute('stories_count', 2);
        $battle->setRelation('user', $creator);
        $battle->setRelation('calledUser', $opponent);
        $battle->setRelation('getMainStory', $creatorStory);
        $battle->setRelation('stories', new Collection([$opponentStory]));
        $battle->setRelation('media', null);

        $data = (new BattleCardResource($battle))->resolve(request());

        $this->assertCount(2, $data['opponents']);
        $this->assertSame('9:16', $data['opponents'][0]['media']['aspect_ratio']);
        $this->assertSame('9:16', $data['opponents'][1]['media']['aspect_ratio']);
        $this->assertSame('opponent', $data['opponents'][1]['author']['username']);
    }

    public function test_campaign_card_prefers_vertical_story_media(): void
    {
        $creator = $this->user(1, 'creator');
        $story = $this->story(30, $creator, $this->media(30, 'video/mp4', 'campaign.mp4'));
        $campaign = new Campaign([
            'id' => 40,
            'title' => 'New camera',
            'slug' => 'new-camera',
            'goal' => 100000,
        ]);
        $campaign->setAttribute('success_payments_sum_amount', 25000);
        $campaign->setRelation('user', $creator);
        $campaign->setRelation('latestActiveStory', $story);
        $campaign->setRelation('success_payments', new Collection());
        $campaign->setRelation('get_category', null);

        $data = (new CampaignCardResource($campaign))->resolve(request());

        $this->assertSame('video', $data['media']['type']);
        $this->assertSame('9:16', $data['media']['aspect_ratio']);
        $this->assertSame(25.0, $data['funding']['progress']);
    }

    private function user(int $id, string $username): User
    {
        return new User([
            'id' => $id,
            'username' => $username,
            'name' => $username,
            'avatar' => '/default_avatars/avatar_1.png',
        ]);
    }

    private function media(int $id, string $mimeType, string $filename): Media
    {
        return new Media([
            'id' => $id,
            'mime_type' => $mimeType,
            'slug' => pathinfo($filename, PATHINFO_FILENAME),
            'slug_ext' => $filename,
            'folder' => 'uploads/tests',
            'thumbnail' => 'uploads/tests/poster.jpg',
        ]);
    }

    private function story(int $id, User $user, Media $media): Story
    {
        $story = new Story([
            'id' => $id,
            'user_id' => $user->id,
            'media_id' => $media->id,
            'active' => true,
            'declined' => false,
        ]);
        $story->setRelation('user', $user);
        $story->setRelation('media', $media);

        return $story;
    }
}
