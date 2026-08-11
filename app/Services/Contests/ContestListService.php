<?php

declare(strict_types=1);

namespace App\Services\Contests;

use App\Models\Abuse;
use App\Models\Battle;
use App\Models\Challenge;
use App\Models\Story;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Facades\Auth;

class ContestListService
{
    public function __construct(
        private readonly ContestAccountInfoCache $accountInfo,
        private readonly ContestVisibilityService $visibility
    )
    {
    }

    public function challenges(Request $request): LengthAwarePaginator
    {
        $query = $this->baseQuery(Challenge::query(), 'challenges');
        $this->applyFilters($query, $request, 'challenge_id');

        return $query->with('user')->orderBy('created_at', 'DESC')->paginate(20);
    }

    public function contests(Request $request): LengthAwarePaginator
    {
        $challengeQuery = $this->baseQuery(Challenge::query(), 'challenges');
        $this->applyFilters($challengeQuery, $request, 'challenge_id');

        $battleQuery = $this->baseQuery(Battle::query(), 'battles');
        $this->applyFilters($battleQuery, $request, 'battle_id', true);

        $items = $challengeQuery->with('user')->get()
            ->concat($battleQuery->with('user')->get())
            ->sortByDesc('created_at')
            ->values();
        $perPage = 20;
        $page = Paginator::resolveCurrentPage();

        return new Paginator(
            $items->forPage($page, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            ['path' => Paginator::resolveCurrentPath(), 'query' => $request->query()]
        );
    }

    public function battles(Request $request): LengthAwarePaginator
    {
        $query = $this->baseQuery(Battle::query(), 'battles');
        $this->applyFilters($query, $request, 'battle_id', true);

        return $query->with('user')->orderBy('created_at', 'DESC')->paginate(20);
    }

    public function formatPaginator(LengthAwarePaginator $items): array
    {
        $data = [];
        foreach ($items as $item) {
            $item->user = $item->user ? $this->accountInfo->build((int) $item->user_id, true) : null;
            $data[] = $item;
        }

        return [
            'success' => true,
            'data' => $data,
            'current_page' => $items->currentPage(),
            'total_pages' => $items->lastPage(),
        ];
    }

    private function baseQuery(Builder $query, string $table): Builder
    {
        $query = $query
            ->where($table . '.active', 1)
            ->where($table . '.declined', 0)
            ->whereNull($table . '.blocked_at');

        return $this->visibility->applyToContests($query, $table, Auth::user() ?? auth()->user());
    }

    private function applyFilters(Builder $query, Request $request, string $storyForeignKey, bool $withoutGlobalScopes = false): void
    {
        $filterType = $request->input('type');
        $user = Auth::user() ?? auth()->user();
        if ($request->input('user_id')) {
            $user = User::find($request->input('user_id'));
        }

        if ($user) {
            $blockedUsers = Abuse::where('abused_by', $user->id)
                ->where('blocked', true)
                ->pluck('user_id')
                ->toArray();

            if ($filterType === 'participant') {
                $storyQuery = $withoutGlobalScopes ? Story::withoutGlobalScopes() : Story::query();
                $contestIds = $storyQuery
                    ->where('user_id', $user->id)
                    ->active()
                    ->whereNotNull($storyForeignKey)
                    ->where(function ($query): void {
                        $query->where('is_main_story', false)
                            ->orWhereNull('is_main_story');
                    })
                    ->pluck($storyForeignKey)
                    ->toArray();

                $query->whereIn('id', $contestIds ?: [0]);
            }

            if ($blockedUsers) {
                $query->whereHas('user', function ($q) use ($blockedUsers): void {
                    $q->whereNotIn('id', $blockedUsers);
                });
            }
        }

        if ($filterType === 'finished') {
            $query->where('finished', true);
        }

        if ($filterType === 'active') {
            $query->where('finished', false);
        }
    }
}
