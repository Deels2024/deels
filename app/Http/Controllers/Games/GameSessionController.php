<?php

namespace App\Http\Controllers\Games;

use App\Http\Controllers\Controller;
use App\Models\Abuse;
use App\Models\Games\Game;
use App\Models\Games\GameSession;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GameSessionController extends Controller
{
    public function list(Request $request)
    {
        $title = 'Игровые сессии';

        $sessions_query = GameSession::query();

        if($request->input('game')) {
            $sessions_query->where('game', $request->input('game'));
        }
        if($request->input('status')) {
            $sessions_query->where('status', $request->input('status'));
        }
        if($request->input('user_id')) {
            $sessions_query->where('user_id', $request->input('user_id'));
        }

        $sessions  = $sessions_query->paginate(20);

        return view('admin.game_sessions', compact('title', 'sessions'));
    }

    public function update(Request $request)
    {

        $type = $request->input('type');
        Game::updateOrCreate(
            ['type' => $type],
            ['settings' => $request->input('settings')]
        );

        return back()->with('success', 'Настройки обновлены');
    }


}