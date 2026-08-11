<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Games;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Http\Controllers\MediaController;
use App\Models\Abuse;
use App\Models\Challenge;
use App\Models\Games\Game;
use App\Models\Games\GameSession;
use App\Models\Likes;
use App\Models\Story;
use App\Models\User;
use Carbon\Carbon;
use FFMpeg\FFMpeg;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Pawlox\VideoThumbnail\VideoThumbnail;

class GameSessionController extends Controller
{

    public function list(Request $request, $type)
    {
        $sessions = GameSession::where('game', $type)->paginate(20);
        return response()->json([
            'success' => true,
            'data' => $sessions->items(),
            'current_page' => $sessions->currentPage(),
            'total_pages' => $sessions->lastPage(),
            'total' => $sessions->total(),
        ]);
    }

    public function get(Request $request, $id)
    {
        $session = GameSession::find($id);
        if ($session) {
            return response()->json([
                'success' => true,
                'data' => $session,
            ]);
        } else {
            return response()->json([
                'success' => false,
                'error' => 'Сессия не найдена',
            ]);
        }
    }

    public function create(Request $request)
    {
        $data = [
            'game' => $request->type,
            'user_id' => $request->user_id,
        ];

        try {
            $session = GameSession::create($data);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Сессия  не создана. Проверьте корректность данных: ' . $th->getMessage(),
            ]);
        }
        return response()->json([
            'success' => true,
            'message' => 'Сессия ' . $session->id . ' создана. Игра '.$request->type,
            'data' => $session
        ]);
    }

    public function update(Request $request, $id)
    {
        $session = GameSession::find($id);
        if ($session) {
            try {
                Log::info($request->input());
                $session->update($request->input());
            } catch (\Throwable $th) {
                return response()->json([
                    'success' => false,
                    'message' => 'Сессия ' . $id . ' не обновлена. Проверьте корректность данных: ' . $th->getMessage(),
                ]);
            }
            return response()->json([
                'success' => true,
                'message' => 'Сессия ' . $id . ' обновлена',
            ]);
        } else {
            return response()->json([
                'success' => false,
                'error' => 'Сессия не найдена',
            ]);
        }
    }


}
