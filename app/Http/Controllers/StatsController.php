<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\Campaign;
use App\Models\Clickhouse\Action;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Story;
use App\Models\WithdrawalRequest;
use App\User;
use Bavix\Wallet\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class StatsController extends Controller
{
    public function index(Request $request)
    {
        // Параметры пагинации
        $perPage = 15;
        $currentPage = request()->input('page', 1);
        $offset = ($currentPage - 1) * $perPage;

// Создаем базовый запрос с фильтрами
        $actions_query = Action::select()
            ->orderBy('created_at', 'DESC');

        if($request->user_id) {
            $actions_query->where('user_id', $request->user_id);
        }
        if($request->username) {
            $user = \App\Models\User::where('username', $request->username)->first();
            if($user) {
                $actions_query->where('user_id', $user->id);
            }
        }
        if($request->type) {
            $actions_query->where('type', $request->type);
        }
        if($request->story_id) {
            $actions_query->where('model', 'Story')->where('model_id', $request->story_id);
        }
        if($request->date_from) {
            $actions_query->where('created_at', '>', $request->date_from);
        }
        if($request->date_to) {
            $actions_query->where('created_at', '<', $request->date_to);
        }

// 1. Получаем общее количество записей С УЧЕТОМ ФИЛЬТРОВ
        $totalCountQuery = clone $actions_query;
        $totalCount = count($totalCountQuery->getRows());

// 2. Получаем данные с пагинацией
        $actions = $actions_query
            ->limit($perPage, $offset)
            ->getRows();

// 3. Загружаем связанные модели
        $userIds = array_unique(array_column($actions, 'user_id'));
        $users = \App\Models\User::whereIn('id', $userIds)->get()->keyBy('id');

        $storyActions = array_filter($actions, fn($a) => ($a['model'] ?? null) === 'Story');
        $storyIds = array_column($storyActions, 'model_id');
        $stories = !empty($storyIds)
            ? Story::whereIn('id', $storyIds)->get()->keyBy('id')
            : collect();

// 4. Объединяем данные
        $items = array_map(function ($action) use ($users, $stories) {
            return array_merge($action, [
                'user' => $users[$action['user_id']] ?? null,
                'story' => ($action['model'] ?? null) === 'Story'
                    ? $stories[$action['model_id']] ?? null
                    : null
            ]);
        }, $actions);

// 5. Создаем пагинатор
        $actions = new LengthAwarePaginator(
            $items,
            $totalCount,
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'query' => request()->query()
            ]
        );


        return view('admin.stats', compact('actions'));
    }

}
