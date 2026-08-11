<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Story;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\Contests\ContestVisibilityService;

class ApiStoriesFeedService
{
    private const PER_PAGE = 8;

    public function __construct(
        private ?RecommendationService $recommendationService = null,
        private ?ContestVisibilityService $contestVisibility = null
    ) {
    }

    public function build(Request $request): ApiStoriesFeedResult
    {
        $this->ensureSessionSeed($request);

        $popular = $request->input('popular');
        $filterType = $request->input('type');
        $userId = $request->input('user_id');
        $excludeIds = $this->parseExcludeIds($request->input('exclude_ids', []));
        $requestedPage = max(1, (int) $request->input('page', 1));
        $effectivePage = !empty($excludeIds) ? 1 : $requestedPage;
        $user = $this->resolveUser($userId);
        $storyIds = $this->recommendedStoryIds($user, $effectivePage);

        if ($filterType === 'popular') {
            $popular = true;
        }

        $mediaQuery = $this->baseQuery((bool) $popular);
        $this->contestVisibility()->applyToStories($mediaQuery, Auth::user() ?? auth()->user());
        $this->applyFilters($mediaQuery, $filterType, (bool) $popular, $request);
        $mediaQuery->excludeBlockedAuthors($user?->id);

        if (!empty($excludeIds)) {
            $mediaQuery->whereNotIn('id', $excludeIds);
            $storyIds = array_values(array_diff($storyIds, $excludeIds));
        }

        if (!empty($storyIds)) {
            $media = $this->recommendedPaginator($mediaQuery, $storyIds, $effectivePage, $request);
        } else {
            $media = $mediaQuery
                ->paginate(self::PER_PAGE, ['*'], 'page', $effectivePage)
                ->appends(request()->query());
        }

        return new ApiStoriesFeedResult($media, $user, $userId, $excludeIds, $requestedPage);
    }

    private function ensureSessionSeed(Request $request): void
    {
        if ($request->session()->has('session_rand')) {
            if ((time() - $request->session()->get('session_rand')) > 3600) {
                $request->session()->put('session_rand', time());
            }

            return;
        }

        $request->session()->put('session_rand', time());
    }

    private function parseExcludeIds($excludeIdsRaw): array
    {
        if (is_string($excludeIdsRaw) && strlen($excludeIdsRaw) > 0) {
            return array_filter(array_map('intval', explode(',', $excludeIdsRaw)));
        }

        if (is_array($excludeIdsRaw)) {
            return array_filter(array_map('intval', $excludeIdsRaw));
        }

        return [];
    }

    private function resolveUser($userId): ?User
    {
        $user = Auth::user() ?? auth()->user() ?? null;

        if ($userId) {
            return User::find($userId);
        }

        return $user;
    }

    private function recommendedStoryIds(?User $user, int $effectivePage): array
    {
        if (!$user) {
            return [];
        }

        $offset = $effectivePage > 1 ? ($effectivePage - 1) * self::PER_PAGE : 0;
        $recommendations = $this->recommendationService()
            ->getRecommendationsForUser($user->id, self::PER_PAGE, $offset);

        return collect($recommendations)
            ->pluck('story_id')
            ->map(fn ($storyId) => (int) $storyId)
            ->unique()
            ->values()
            ->toArray();
    }

    private function recommendationService(): RecommendationService
    {
        if (!$this->recommendationService) {
            $this->recommendationService = new RecommendationService();
        }

        return $this->recommendationService;
    }

    private function contestVisibility(): ContestVisibilityService
    {
        return $this->contestVisibility ??= app(ContestVisibilityService::class);
    }

    private function baseQuery(bool $popular)
    {
        if ($popular) {
            return Story::with('comments', 'likes')
                ->where('active', true)
                ->where('declined', false)
                ->withCount('comments', 'likes', 'views')
                ->orderBy('views_count', 'desc')
                ->orderBy('likes_count', 'desc')
                ->orderBy('comments_count', 'desc')
                ->orderBy('created_at', 'DESC');
        }

        return Story::withCount('comments', 'likes')
            ->whereHas('user')
            ->where('active', true)
            ->where('declined', false);
    }

    private function applyFilters($mediaQuery, $filterType, bool $popular, Request $request): void
    {
        if ($filterType === 'new') {
            $mediaQuery->orderBy('created_at', 'DESC');
        }

        if ($filterType === 'paid') {
            $mediaQuery->where('paid', true)->where('amount', '>', 0);
        }

        if (!$filterType && !$popular) {
            $mediaQuery->orderBy(DB::raw('RAND(' . $request->session()->get('session_rand') . ')'));
        }
    }

    private function recommendedPaginator($mediaQuery, array $storyIds, int $page, Request $request): LengthAwarePaginator
    {
        $recommendedMedia = (clone $mediaQuery)
            ->whereIn('id', $storyIds)
            ->get()
            ->sortBy(function ($story) use ($storyIds) {
                $position = array_search($story->id, $storyIds, true);

                return $position === false ? PHP_INT_MAX : $position;
            })
            ->values();

        $remaining = max(0, self::PER_PAGE - $recommendedMedia->count());
        $existingIds = $recommendedMedia->pluck('id')->toArray();

        $additionalMedia = collect();
        if ($remaining > 0) {
            $fallbackOffset = ($page - 1) * self::PER_PAGE;
            $additionalMedia = (clone $mediaQuery)
                ->whereNotIn('id', $existingIds)
                ->offset($fallbackOffset)
                ->limit($remaining)
                ->get();
        }

        $combinedResults = $recommendedMedia->concat($additionalMedia)->take(self::PER_PAGE)->values();

        return new LengthAwarePaginator(
            $combinedResults,
            $mediaQuery->count(),
            self::PER_PAGE,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
    }
}
