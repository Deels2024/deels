<?php

namespace App\Http\Controllers\Dashboard;

use App\Jobs\FinishChallenge;
use App\Models\Battle;
use App\Models\Challenge;
use App\Models\Story;
use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ChallengeDashboardController
{

    public function create(): Factory|View|Application
    {
        $title = 'Создать челлендж';
        $challange_coin = false;
        return view('challenges.create', compact('title', 'challange_coin'));
    }

    public function inviteUsers(Request $request)
    {
        $user = Auth::user() ?? auth()->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'error' => 'Вы не авторизованы',
            ], 401);
        }

        $friendIds = DB::table('followables')
            ->where('followable_id', $user->id)
            ->pluck('user_id')
            ->intersect(
                DB::table('followables')
                    ->where('user_id', $user->id)
                    ->pluck('followable_id')
            )
            ->values()
            ->toArray();

        $search = trim((string) $request->input('q', ''));
        $baseQuery = User::query()
            ->where('id', '!=', $user->id)
            ->whereNull('deleted_at');
        $excludedIds = collect(explode(',', (string) $request->input('exclude_ids', '')))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();
        if ($excludedIds) {
            $baseQuery->whereNotIn('id', $excludedIds);
        }
        $selectedIds = collect(explode(',', (string) $request->input('ids', '')))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->toArray();
        $selectedUsers = $selectedIds
            ? (clone $baseQuery)->whereIn('id', $selectedIds)->get()
            : collect();

        if ($search !== '') {
            $usersQuery = (clone $baseQuery)
                ->where(function ($query) use ($search): void {
                    $query->where('username', 'like', '%' . $search . '%')
                        ->orWhere('name', 'like', '%' . $search . '%')
                        ->orWhere('last_name', 'like', '%' . $search . '%');
                });
            if ($request->boolean('friends_only')) {
                $usersQuery->whereIn('id', $friendIds ?: [0]);
            }
            $users = $usersQuery->limit(20)->get();

            return response()->json([
                'success' => true,
                'friends' => [],
                'random' => [],
                'results' => $this->formatInviteUsers($users, $friendIds),
                'selected' => $this->formatInviteUsers($selectedUsers, $friendIds),
            ]);
        }

        $friends = (clone $baseQuery)
            ->whereIn('id', $friendIds ?: [0])
            ->inRandomOrder()
            ->limit(10)
            ->get();

        $randomQuery = clone $baseQuery;
        if ($request->boolean('friends_only')) {
            $randomQuery->whereRaw('1 = 0');
        }
        if (!$request->boolean('all_random')) {
            $randomQuery->whereNotIn('id', $friendIds ?: [0]);
        }
        $random = $randomQuery->inRandomOrder()->limit(100)->get();

        return response()->json([
            'success' => true,
            'friends' => $this->formatInviteUsers($friends, $friendIds),
            'random' => $this->formatInviteUsers($random, $friendIds),
            'results' => [],
            'selected' => $this->formatInviteUsers($selectedUsers, $friendIds),
        ]);
    }

    private function formatInviteUsers($users, array $friendIds): array
    {
        return $users->map(function (User $user) use ($friendIds): array {
            return [
                'id' => $user->id,
                'username' => $user->username,
                'name' => $user->fullname ?: $user->username,
                'avatar' => url($user->avatar()),
                'is_friend' => in_array($user->id, $friendIds),
            ];
        })->values()->toArray();
    }

    public function edit($id)
    {
        $challenge = Challenge::findOrFail($id);
        $user = Auth::user() ?? auth()->user() ?? null;
        if (!$user) {
            abort(404);
        }
        if (!$challenge) {
            abort(404);
        }
        if ($user->id != $challenge->user_id && !$user->is_admin()) {
            abort(404);
        }
        $title = 'Редактировать челлендж';
        $challange_coin = false;
        return view('challenges.create', compact('title', 'challenge', 'challange_coin'));
    }



    public function show($id)
    {
        $challenge = Challenge::findOrFail($id);
        if ($challenge->user_id != Auth::user()->id) {
            abort(404);
        }
        $title = 'Челлендж ' . $challenge->title;
        $stories = $challenge->stories()->paginate(8);
        $stories_count = $challenge->stories()->count();
        return view('challenges.page', compact('challenge', 'title', 'stories', 'stories_count'));
    }

    public function list(Request $request)
    {
        $title = 'Мои челенджи';
        $user = request()->user();
        $type = $request->type;
        $challenges_query = $user->challenges();
        $battles_query = Battle::query()->where('user_id', $user->id);
        $my_challenges_stories = Story::where('user_id', $user->id)
            ->whereNotNull('challenge_id')
            ->where(function ($query): void {
                $query->where('is_main_story', false)
                    ->orWhereNull('is_main_story');
            })
            ->pluck('challenge_id')
            ->toArray();
        if($type == 'participant') {
            $title = 'Я участвую';
            $challenges_query = Challenge::query()->where('active',1);
            $battles_query = Battle::query()->where('active', 1);
            if (!empty($my_challenges_stories)) {
                $challenges_query->whereIn('id', $my_challenges_stories);
            } else {
                $challenges_query->whereIn('id', [0]);
            }

            $my_battle_stories = Story::withoutGlobalScopes()
                ->where('user_id', $user->id)
                ->whereNotNull('battle_id')
                ->where(function ($query): void {
                    $query->where('is_main_story', false)
                        ->orWhereNull('is_main_story');
                })
                ->pluck('battle_id')
                ->toArray();
            $battles_query->whereIn('id', $my_battle_stories ?: [0]);
        }

        $items = $challenges_query->with('user')->get()
            ->concat($battles_query->with('user')->get())
            ->sortByDesc('created_at')
            ->values();
        $perPage = 12;
        $page = LengthAwarePaginator::resolveCurrentPage();
        $challenges = new LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            ['path' => LengthAwarePaginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        return view('dashboard.challenges.challenges_index', compact('user', 'challenges', 'title'));
    }

    public function remove(Request $request)
    {
        $user = Auth::user() ?? auth()->user() ?? null;
        $challenge = Challenge::find($request->challenge_id);
        if (!$user) {
            return ['success' => false, 'error' => 'Пользователь не найден'];
        }
        if (!$challenge) {
            return ['success' => false, 'error' => 'Челлендж не найдена'];
        }
        if ($user->id != $challenge->user_id && !$user->is_admin() && !$user->is_comment_admin()) {
            return ['success' => false, 'error' => 'Вы не можете удалить этот челлендж'];
        }
        $challenge->delete();
        return ['success' => true, 'message' => 'Челлендж удален'];
    }

    public function stop($id)
    {
        $user = Auth::user() ?? auth()->user() ?? null;
        $challenge = Challenge::find($id);
        if (!$user) {
            abort(404);
        }
        if (!$challenge) {
            abort(404);
        }
        if ($user->id != $challenge->user_id && !$user->is_admin() && !$user->is_comment_admin()) {
            abort(404);
        }

        if(!$challenge->finished) {
            FinishChallenge::dispatchSync($challenge);
            $challenge->finished = true;
            $challenge->saveQuietly();
            return redirect()->back()->with(['success' => 'Ваш челлендж завершен!']);
        } else {
            return redirect()->back()->with(['fail' => 'Ваш челлендж уже завершен!']);
        }


    }


}
