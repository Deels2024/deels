<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Jobs\FinishBattle;
use App\Models\Battle;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardBattlesController extends Controller
{
    public function index(Request $request)
    {
        $title = 'Модерация батлов';

        $user = Auth::user();

        $battles = [];

        $type = $request->input('type');
        $challenge_id = $request->input('challenge_id');
        $ai_moderated = $request->input('ai_moderated');


        if ($user->is_admin() || $user->is_comment_admin()) {
            $data_query = Battle::query();


            $battles = $data_query->orderBy('id', 'desc')->paginate(12);
        }


        return view('admin.battles', compact('title', 'battles'));
    }

    public function create(): Factory|View|Application
    {
        $title = 'Создать батл';
        $activeTab = 'battle';
        $challange_coin = false;
        return view('challenges.create', compact('title', 'activeTab', 'challange_coin'));
    }

    public function edit($id)
    {
        $battle = Battle::findOrFail($id);
        $user = Auth::user() ?? auth()->user() ?? null;
        if (!$user) {
            abort(404);
        }
        if (!$battle) {
            abort(404);
        }
        if ($user->id != $battle->user_id && !$user->is_admin()) {
            abort(404);
        }
        $title = 'Редактировать батл';
        $activeTab = 'battle';
        $challange_coin = false;
        return view('challenges.create', compact('title', 'battle', 'activeTab', 'challange_coin'));
    }


    public function show($id)
    {
        $battle = Battle::findOrFail($id);
        $user = Auth::user();

        if ((int) $battle->user_id !== (int) $user->id
            && !$user->is_admin()
            && !$user->is_comment_admin()) {
            abort(404);
        }

        $title = 'Батл ' . $battle->title;
        $stories = $battle->stories()->paginate(8);
        $stories_count = $battle->stories()->count();
        return view('battles.page', compact('battle', 'title', 'stories', 'stories_count'));
    }


    public function remove(Request $request)
    {
        $user = Auth::user() ?? auth()->user() ?? null;
        $battle = Battle::find($request->battle_id);
        if (!$user) {
            return ['success' => false, 'error' => 'Пользователь не найден'];
        }
        if (!$battle) {
            return ['success' => false, 'error' => 'Батл не найдена'];
        }
        if ($user->id != $battle->user_id && !$user->is_admin() && !$user->is_comment_admin()) {
            return ['success' => false, 'error' => 'Вы не можете удалить этот батл'];
        }
        $battle->delete();
        return ['success' => true, 'message' => 'Батл удален'];
    }

    public function stop($id)
    {
        $user = Auth::user() ?? auth()->user() ?? null;
        $battle = Battle::find($id);
        if (!$user) {
            abort(404);
        }
        if (!$battle) {
            abort(404);
        }
        if ($user->id != $battle->user_id && !$user->is_admin() && !$user->is_comment_admin()) {
            abort(404);
        }

        if (!$battle->finished) {
            FinishBattle::dispatchSync($battle);
            $battle->finished = true;
            $battle->saveQuietly();
            return redirect()->back()->with(['success' => 'Ваш батл завершен!']);
        } else {
            return redirect()->back()->with(['fail' => 'Ваш батл уже завершен!']);
        }


    }

}
