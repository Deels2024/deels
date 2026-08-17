<?php

declare(strict_types=1);

namespace App\Services\Home;

use App\Models\Battle;
use App\Models\Campaign;
use App\Models\Category;
use App\Models\Challenge;
use App\Models\Comment;
use App\Models\Payment;
use App\Models\Story;
use App\Models\User;
use App\Models\View;
use App\Repositories\CampaignRepository;
use App\Services\Contests\ContestVisibilityService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HomePageDataService
{
    public const TITLE = 'Deels — платформа челленджей, батлов и творчества';

    public const DESCRIPTION = 'Участвуйте в челленджах и батлах, публикуйте вертикальные видео, голосуйте за участников, получайте награды и поддерживайте авторов на Deels.';

    public function __construct(
        private readonly CampaignRepository $campaigns,
        private readonly ContestVisibilityService $visibility,
        private readonly DeelsBankService $bank
    ) {
    }

    public function get(?User $viewer = null): array
    {
        $stories = $this->storyBlocks($viewer);
        $stats = $this->stats();
        $campaignLimit = (int) config('homepage.limits.campaigns', 8);
        $fundedCampaigns = $this->campaigns->fundedCampaigns($campaignLimit);

        return array_merge([
            'title' => self::TITLE,
            'description' => self::DESCRIPTION,
            'bank' => $this->bank->balance(),
            'completedCampaigns' => $this->completedCampaigns(),
            'newStories' => $stories['newStories'],
            'donateStories' => $stories['donateStories'],
            'topStories' => $stories['topStories'],
            'topChallenges' => $this->topChallenges($viewer),
            'topBattles' => $this->topBattles($viewer),
            'popularDirections' => $this->popularDirections(),
            'fundedCampaigns' => $fundedCampaigns,
            'newCampaigns' => $this->campaigns->newCampaigns($campaignLimit, $fundedCampaigns->pluck('id')),
            'latestFundedCampaigns' => $this->campaigns->latestFundedCampaigns($campaignLimit),
            'stats' => $stats,
        ], $stats);
    }

    /**
     * Preserve the legacy JSON contract used by clients that request `/`
     * with an Accept: application/json header. New clients should use
     * `/api/v1/home` instead.
     */
    public function legacyJson(array $data): array
    {
        return [
            'title' => $data['title'],
            'description' => $data['description'],
            'newCampaigns' => $data['newCampaigns'],
            'fundedCampaigns' => $data['fundedCampaigns'],
            'campaignsCount' => $data['campaignsCount'],
            'usersCount' => $data['usersCount'],
            'fundRaised' => $data['fundRaised'],
            'latestFundedCampaigns' => $data['latestFundedCampaigns'],
            'fundedCampaignsCount' => $data['fundedCampaignsCount'],
            'completedCampaigns' => $data['completedCampaigns'],
            'storiesCount' => $data['storiesCount'],
            'storiesDonatedCount' => $data['storiesDonatedCount'],
            'storiesCommentsCount' => $data['storiesCommentsCount'],
            'storiesViewsCount' => $data['storiesViewsCount'],
            'topDonaters' => [],
            'topReferrals' => [],
            'topWinners' => [],
            'doneCampaigns' => [],
            'topChallenges' => $data['topChallenges'],
        ];
    }

    private function storyBlocks(?User $viewer): array
    {
        $ttl = $this->ttl();
        $limit = (int) config('homepage.limits.stories', 10);
        $today = Carbon::today();
        $weekAgo = $today->copy()->subDays(7);
        $twoWeeksAgo = $today->copy()->subDays(14);

        $blocks = Cache::remember('home.blocks.stories:v2', $ttl, function () use ($limit, $today, $weekAgo, $twoWeeksAgo): array {
            $newStories = $this->storyQuery()
                ->whereBetween('stories.created_at', [$weekAgo->startOfDay(), $today->copy()->endOfDay()])
                ->latest('stories.created_at')
                ->limit($limit)
                ->get();

            if ($newStories->isEmpty()) {
                $newStories = $this->storyQuery()
                    ->whereBetween('stories.created_at', [$twoWeeksAgo->startOfDay(), $today->copy()->endOfDay()])
                    ->latest('stories.created_at')
                    ->limit($limit)
                    ->get();
            }

            $donateStories = $this->storyQuery()
                ->where('stories.amount', '>', 0)
                ->where('stories.paid', true)
                ->inRandomOrder()
                ->limit($limit)
                ->get();

            $topStories = collect();
            foreach ([0, 7, 14, null] as $days) {
                $query = $this->storyQuery()
                    ->orderByDesc('comments_count')
                    ->orderByDesc('likes_count')
                    ->orderByDesc('views_count')
                    ->latest('stories.created_at');

                if ($days === 0) {
                    $query->whereBetween('stories.created_at', [$today->copy()->startOfDay(), $today->copy()->endOfDay()]);
                } elseif ($days !== null) {
                    $query->whereBetween('stories.created_at', [$today->copy()->subDays($days)->startOfDay(), $today->copy()->endOfDay()]);
                }

                $topStories = $query->limit($limit)->get();
                if ($topStories->count() >= 6 || $days === null) {
                    break;
                }
            }

            return compact('newStories', 'donateStories', 'topStories');
        });

        foreach ($blocks as $key => $items) {
            $blocks[$key] = $this->prepareStoriesForViewer($items, $viewer);
        }

        return $blocks;
    }

    private function storyQuery(): Builder
    {
        return Story::query()
            ->active()
            ->with(['user', 'media', 'challenge', 'battle'])
            ->withCount(['comments', 'likes', 'views']);
    }

    private function prepareStoriesForViewer(Collection $stories, ?User $viewer): Collection
    {
        $visible = $stories->filter(function (Story $story) use ($viewer): bool {
            $contest = $story->challenge_id
                ? $story->challenge
                : ($story->battle_id ? $story->battle : null);

            return !$contest || $this->visibility->canView($contest, $viewer);
        })->values();

        $viewedIds = collect();
        if ($viewer && $visible->isNotEmpty()) {
            $viewedIds = View::where('user_id', $viewer->id)
                ->whereIn('story_id', $visible->pluck('id'))
                ->pluck('story_id')
                ->map(static fn ($id): int => (int) $id);
        }

        return $visible->each(static function (Story $story) use ($viewedIds): void {
            $story->setAttribute('is_viewed', $viewedIds->contains((int) $story->id));
        });
    }

    private function topChallenges(?User $viewer): Collection
    {
        return Cache::remember(
            'home.blocks.top_challenges:v2:' . ($viewer?->id ?? 'guest'),
            $this->ttl(),
            function () use ($viewer): Collection {
                $query = Challenge::active()->whereNull('challenges.finished_at');
                $this->visibility->applyToContests($query, 'challenges', $viewer);

                return $query
                    ->with(['user', 'media', 'winners', 'getMainStory.media'])
                    ->withCount(['comments', 'likes', 'views', 'stories'])
                    ->orderByDesc('views_count')
                    ->orderByDesc('likes_count')
                    ->orderByDesc('comments_count')
                    ->latest('challenges.created_at')
                    ->limit((int) config('homepage.limits.challenges', 10))
                    ->get();
            }
        );
    }

    private function topBattles(?User $viewer): Collection
    {
        return Cache::remember(
            'home.blocks.top_battles:v2:' . ($viewer?->id ?? 'guest'),
            $this->ttl(),
            function () use ($viewer): Collection {
                $query = Battle::active();
                $this->visibility->applyToContests($query, 'battles', $viewer);

                return $query
                    ->with([
                        'user',
                        'calledUser',
                        'media',
                        'getMainStory.media',
                        'getMainStory.user',
                        'stories' => static function ($query): void {
                            $query->active()
                                ->with(['user', 'media'])
                                ->withCount(['comments', 'likes', 'dislikes', 'views'])
                                ->latest('stories.created_at');
                        },
                    ])
                    ->withCount(['comments', 'likes', 'views', 'stories'])
                    ->orderByDesc('views_count')
                    ->orderByDesc('likes_count')
                    ->latest('battles.created_at')
                    ->limit((int) config('homepage.limits.battles', 6))
                    ->get();
            }
        );
    }

    private function completedCampaigns(): Collection
    {
        return Cache::remember('home.hero.completed_campaigns:v2', now()->addMinutes(30), static function (): Collection {
            return Campaign::whereIn('slug', [
                'kreslo-gamak',
                'bilet-na-koncert-mukki',
                'na-kraski-dlia-novoi-kartiny',
                'na-obucenie-tatu-mastera',
                'donat-v-frostborn',
                'buket-romasek',
                'dorado',
                'obucenie-52',
            ])->with(['user', 'success_payments', 'feature_media'])->get();
        });
    }

    private function popularDirections(): Collection
    {
        return Cache::remember('home.blocks.popular_directions:v1', now()->addMinutes(30), static function (): Collection {
            return Category::query()
                ->withCount('campaigns')
                ->having('campaigns_count', '>', 0)
                ->orderByDesc('campaigns_count')
                ->orderBy('category_name')
                ->limit((int) config('homepage.limits.directions', 6))
                ->get();
        });
    }

    private function stats(): array
    {
        return Cache::remember('home.blocks.stats:v2', $this->ttl(), static function (): array {
            $campaignsCount = Campaign::active()->count();
            $usersCount = User::count();
            $fundRaised = (float) Payment::whereStatus('success')->sum('amount');
            $fundedCampaignsCount = Payment::whereStatus('success')
                ->whereNotNull('campaign_id')
                ->distinct()
                ->count('campaign_id');
            $storiesCount = Story::active()->count();
            $storiesDonatedCount = (int) DB::table('transactions')
                ->where('meta', 'like', '%{"get":"story"%')
                ->sum('amount');
            $storiesCommentsCount = Comment::whereNotNull('story_id')->where('approved', true)->count();
            $storiesViewsCount = View::count();
            $challengesCount = Challenge::active()->count();
            $battlesCount = Battle::active()->count();
            $participantsCount = Schema::hasTable('contest_participations')
                ? DB::table('contest_participations')->where('status', 'active')->distinct()->count('user_id')
                : 0;
            $rewardsTotal = 0;
            if (Schema::hasColumn('challenges', 'reward_amount')) {
                $rewardsTotal += (int) Challenge::whereNotNull('reward_amount')->sum('reward_amount');
            }
            if (Schema::hasColumn('battles', 'reward_amount')) {
                $rewardsTotal += (int) Battle::whereNotNull('reward_amount')->sum('reward_amount');
            }

            return compact(
                'campaignsCount',
                'usersCount',
                'fundRaised',
                'fundedCampaignsCount',
                'storiesCount',
                'storiesDonatedCount',
                'storiesCommentsCount',
                'storiesViewsCount',
                'challengesCount',
                'battlesCount',
                'participantsCount',
                'rewardsTotal'
            );
        });
    }

    private function ttl(): Carbon
    {
        return now()->addSeconds((int) config('homepage.cache_ttl_seconds', 300));
    }
}
