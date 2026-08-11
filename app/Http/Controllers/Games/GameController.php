<?php

namespace App\Http\Controllers\Games;

use App\Http\Controllers\Controller;
use App\Models\Abuse;
use App\Models\Games\Game;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GameController extends Controller
{
    public function index(Request $request)
    {
        $title = 'Настройки игр';

        $user = Auth::user();

        $chests = Game::where('type', 'chests')->first();
        $wheel = Game::where('type', 'wheel')->first();


        return view('admin.games', compact('title', 'chests', 'wheel'));
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