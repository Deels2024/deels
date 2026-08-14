<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Home\BattleCardResource;
use App\Http\Resources\Home\CampaignCardResource;
use App\Http\Resources\Home\ChallengeCardResource;
use App\Http\Resources\Home\StoryCardResource;
use App\Models\Category;
use App\Services\Home\HomePageDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

final class HomePageController extends Controller
{
    public function __invoke(Request $request, HomePageDataService $homePage): JsonResponse
    {
        $viewer = Auth::guard('sanctum')->user() ?? $request->user();
        $data = $homePage->get($viewer);
        $bank = (int) $data['bank'];

        return response()->json([
            'success' => true,
            'version' => '1.0',
            'data' => [
                'meta' => [
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'section_order' => [
                        'hero',
                        'bank',
                        'challenges',
                        'how_to_participate',
                        'top_stories',
                        'donation_stories',
                        'new_stories',
                        'battles',
                        'directions',
                        'recently_funded_campaigns',
                        'new_campaigns',
                        'why_deels',
                        'benefits',
                        'cta',
                    ],
                ],
                'links' => [
                    'challenges' => route('challenges.catalog'),
                    'battles' => route('battles.catalog'),
                    'stories' => route('stories.catalog'),
                    'campaigns' => route('browse_campaign'),
                    'create_challenge' => route('challenges.create'),
                    'create_battle' => route('battles.create'),
                ],
                'bank' => [
                    'value' => $bank,
                    'formatted' => str_pad((string) $bank, 8, '0', STR_PAD_LEFT),
                    'refresh_url' => route('coins_bank'),
                ],
                'metrics' => $data['stats'],
                'filters' => [
                    'challenges' => [
                        ['id' => 'all', 'title' => 'Все', 'url' => route('challenges.catalog')],
                        ['id' => 'rewarded', 'title' => 'С призами', 'url' => route('challenges.catalog', ['type' => 'rewarded'])],
                        ['id' => 'new', 'title' => 'Новые', 'url' => route('challenges.catalog', ['type' => 'new'])],
                        ['id' => 'ending', 'title' => 'Завершаются', 'url' => route('challenges.catalog', ['type' => 'ending'])],
                    ],
                    'battles' => [
                        ['id' => 'active', 'title' => 'Активные', 'url' => route('battles.catalog', ['type' => 'active'])],
                        ['id' => 'finished', 'title' => 'Завершённые', 'url' => route('battles.catalog', ['type' => 'finished'])],
                    ],
                ],
                'sections' => [
                    'challenges' => $this->resources(ChallengeCardResource::class, $data['topChallenges'], $request),
                    'top_stories' => $this->resources(StoryCardResource::class, $data['topStories'], $request),
                    'donation_stories' => $this->resources(StoryCardResource::class, $data['donateStories'], $request),
                    'new_stories' => $this->resources(StoryCardResource::class, $data['newStories'], $request),
                    'battles' => $this->resources(BattleCardResource::class, $data['topBattles'], $request),
                    'directions' => $data['popularDirections']->map(static fn (Category $category): array => [
                        'id' => (int) $category->id,
                        'title' => $category->category_name,
                        'slug' => $category->slug,
                        'url' => route('campaigns.category', ['slug' => $category->slug]),
                        'image' => $category->get_image_url(),
                        'campaigns_count' => (int) $category->campaigns_count,
                    ])->values()->all(),
                    'recently_funded_campaigns' => $this->resources(CampaignCardResource::class, $data['latestFundedCampaigns'], $request),
                    'new_campaigns' => $this->resources(CampaignCardResource::class, $data['newCampaigns'], $request),
                ],
            ],
        ]);
    }

    /** @param class-string<JsonResource> $resource */
    private function resources(string $resource, Collection $items, Request $request): array
    {
        return $items
            ->map(static fn ($item): array => (new $resource($item))->resolve($request))
            ->values()
            ->all();
    }
}
