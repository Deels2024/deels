<?php

declare(strict_types=1);

namespace App\Services\Stories;

use App\Models\Campaign;
use App\Models\Likes;
use App\Models\Story;
use App\Models\User;
use Carbon\Carbon;

class StoryAccountInfoService
{
    public function build($id = null): ?array
    {
        $user = $id ? User::find($id) : auth()->user();

        if (!$user) {
            return null;
        }

        $gender = 'Не указан';

        if ($user->gender == 'female') {
            $gender = 'Женский';
        }
        if ($user->gender == 'male') {
            $gender = 'Мужской';
        }

        $lastCampaignsData = [];
        $lastCampaigns = $user->my_campaigns()->orderBy('created_at', 'desc')->get()->take(5);
        foreach ($lastCampaigns as $campaign) {
            $daysLeft = null;
            $endDate = Carbon::parse($campaign->end_date);
            $now = Carbon::now();
            if (!$endDate->isPast()) {
                $daysLeft = $now->diffInDays($endDate);
            }
            $lastCampaignsData[] = [
                'id' => $campaign->id,
                'preview' => $campaign->feature_img_url()->feature_image,
                'goal' => $campaign->goal,
                'donated' => $campaign->success_payments->sum('amount'),
                'sponsors' => $campaign->success_payments->count(),
                'days_left' => $daysLeft,
            ];
        }

        $dataStories = [];
        foreach ($user->stories()->get() as $mediaItem) {
            $mediaItem->campaign = $mediaItem->campaign();
            $dataStories[] = $mediaItem;
        }

        $storiesIds = Story::where('user_id', $user->id)->pluck('id')->toArray();
        $campaignsIds = Campaign::where('user_id', $user->id)->pluck('id')->toArray();
        $likesCount = Likes::whereIn('campaign_id', $campaignsIds)->orWhereIn('story_id', $storiesIds)->count();

        return [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'fullname' => $user->fullname,
            'email' => $user->email,
            'trust_rating' => $user->trust_rating ?? 0,
            'country' => $user->country->name_ru ?? null,
            'gender' => $gender,
            'address' => $user->address ?? null,
            'website' => $user->website ?? null,
            'phone' => $user->phone ?? null,
            'avatar' => url($user->avatar()),
            'avatar_url' => url($user->avatar()),
            'donated' => $user->contributed_amount(),
            'campaigns_total' => $user->my_campaigns()->count(),
            'last_campaigns' => $lastCampaignsData,
            'stories' => $dataStories,
            'likes_count' => $likesCount,
        ];
    }
}
