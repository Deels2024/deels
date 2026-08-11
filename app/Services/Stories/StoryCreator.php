<?php

declare(strict_types=1);

namespace App\Services\Stories;

use App\Models\Story;
use App\Models\User;
use Illuminate\Http\Request;

class StoryCreator
{
    public function create(Request $request, $userId, $mediaId, bool $paid, array $adsData): Story
    {
        $requestData = $request->data ?? null;
        if ($requestData) {
            $requestData = json_decode($requestData, true);
        }

        $user = User::find($userId);

        return Story::create([
            'user_id' => $userId,
            'description' => $request->description,
            'data' => $requestData,
            'amount' => is_numeric($request->input('amount')) ? (int) $request->input('amount') : 0,
            'paid' => $paid,
            'challenge_id' => $request->challenge_id,
            'battle_id' => $request->battle_id,
            'is_useful' => $request->boolean('is_useful'),
            'campaign_id' => $request->campaign_id,
            'media_id' => $mediaId,
            'is_ad' => $request->input('is_ad') ?? 0,
            'ads_data' => $adsData,
            'ip_address' => $user->ip_address ?? [],
        ]);
    }
}
