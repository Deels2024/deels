<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Abuse;
use App\Models\Campaign;
use App\Models\Challenge;
use App\Models\Likes;
use App\Models\Story;
use App\Models\User;
use App\Services\Contests\ContestVisibilityService;
use App\Services\Contests\ProfileContestService;
use Illuminate\Support\Facades\Auth;

class ApiAccountInfoService
{
    public function build($id = null, bool $justUserInfo = false): ?array
    {
        $showAll = false;
        if (! $id) {
            $user = auth()->user();
            $showAll = true;
        } else {
            $user = User::find($id);
        }

        if (! $user) {
            return null;
        }

        $lastCampaignsData = [];
        $dataStories = [];
        $dataChallenges = [];
        $dataChallengesParticipant = [];

        if (! $justUserInfo) {
            $lastCampaignsData = $this->lastCampaignsData($user);
            [$dataStories, $dataChallenges, $dataChallengesParticipant] = $this->activityData($user, $showAll);
        }

        $userData = $this->baseUserData($user, $lastCampaignsData);

        if (! $justUserInfo) {
            $userData['campaigns_total'] = $user->my_campaigns()->count();
            $userData['donated'] = $user->contributed_amount();
            $userData['challenges'] = $dataChallenges;
            $userData['challenges_participant'] = $dataChallengesParticipant;
            $contestProfile = app(ProfileContestService::class)
                ->forProfile($user, Auth::user() ?? auth()->user());
            $userData['contests'] = $contestProfile['contests'];
            $userData['hidden_contests_count'] = $contestProfile['hidden_count'];
            $userData['stories'] = $dataStories;
            $userData['last_campaigns'] = $lastCampaignsData;
            $userData['likes_count'] = $this->likesCount($user);
        }

        if ((int) auth()->id() === (int) $user->id) {
            $userData['events'] = $user->events()
                ->pending()
                ->latest()
                ->get()
                ->map(fn ($event): array => [
                    'id' => $event->id,
                    'type' => $event->type,
                    'result' => $event->result,
                    'data' => $event->data,
                    'created_at' => $event->created_at?->toIso8601String(),
                    'expires_at' => $event->expires_at?->toIso8601String(),
                ])
                ->values();
        }

        return $userData;
    }

    private function lastCampaignsData(User $user)
    {
        return $user->my_campaigns()
            ->active()
            ->orderBy('created_at', 'desc')
            ->get()
            ->take(5)
            ->transform(fn ($campaign) => Campaign::formatCampaignData($campaign, true));
    }

    private function activityData(User $user, bool $showAll): array
    {
        $viewer = Auth::user() ?? auth()->user();
        $visibility = app(ContestVisibilityService::class);
        if ($showAll) {
            $storiesQuery = $user->stories();
            $challengesQuery = $user->challenges();
        } else {
            $storiesQuery = $user->stories()->orderBy('created_at', 'DESC')->where('active', true);
            $challengesQuery = $user->challenges()->orderBy('created_at', 'DESC')->active();
        }
        $stories = $visibility->applyToStories($storiesQuery->getQuery(), $viewer)->get();
        $challenges = $visibility->applyToContests($challengesQuery, 'challenges', $viewer)->get();

        $storyChallengeIds = collect($stories)->whereNotNull('challenge_id')->pluck('challenge_id')->toArray();
        $participantChallenges = [];
        if (! empty($storyChallengeIds)) {
            $participantQuery = Challenge::whereIn('id', $storyChallengeIds);
            $participantChallenges = $visibility->applyToContests($participantQuery, 'challenges', $viewer)->get();
        }

        $dataStories = [];
        foreach ($stories as $story) {
            $story->campaign = $story->campaign();
            $dataStories[] = $story;
        }

        $dataChallenges = [];
        foreach ($challenges as $challenge) {
            $dataChallenges[] = $challenge;
        }

        return [$dataStories, $dataChallenges, $participantChallenges];
    }

    private function baseUserData(User $user, $lastCampaignsData): array
    {
        $blocked = false;
        $abuserId = Auth::id() ?? auth()->id() ?? null;
        if ($abuserId) {
            $abuse = Abuse::where('abused_by', $abuserId)->where('user_id', $user->id)->first();
            $blocked = (bool) ($abuse?->blocked);
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'status' => $user->status,
            'trust_rating' => $user->trust_rating ?? 0,
            'limits' => $user->limits ?? null,
            'country' => $user->country->name_ru ?? null,
            'gender' => $this->genderTitle($user->gender),
            'address' => $user->address,
            'website' => $user->website,
            'phone' => $user->phone,
            'avatar' => url($user->avatar()),
            'avatar_url' => url($user->avatar()),
            'last_campaigns' => $lastCampaignsData,
            'wallet_balance' => $user->wallet_balance ?? 0,
            'profit_balance' => $user->profit_balance ?? 0,
            'withdraw_balance' => $user->withdraw_balance ?? 0,
            'wallets' => $user->wallets ?? null,
            'is_activated' => $user->is_activated ?? 0,
            'blocked_by_user' => $blocked,
            'need_actions' => app(SuspiciousAccountService::class)->needActions($user),
            'suspicious_blocked_until' => $user->suspicious_blocked_until?->toIso8601String(),
            'suspicious_retry_after' => $user->suspicious_blocked_until?->isFuture()
                ? now()->diffInSeconds($user->suspicious_blocked_until)
                : 0,
        ];
    }

    private function genderTitle(?string $gender): string
    {
        if ($gender === 'female') {
            return 'Женский';
        }

        if ($gender === 'male') {
            return 'Мужской';
        }

        return 'Не указан';
    }

    private function likesCount(User $user): int
    {
        $storyIds = Story::where('user_id', $user->id)->pluck('id')->toArray();
        $campaignIds = Campaign::where('user_id', $user->id)->pluck('id')->toArray();

        return Likes::whereIn('campaign_id', $campaignIds)
            ->orWhereIn('story_id', $storyIds)
            ->count();
    }
}
