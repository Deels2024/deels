<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Games;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Http\Controllers\MediaController;
use App\Models\Abuse;
use App\Models\Challenge;
use App\Models\Games\Game;
use App\Models\Likes;
use App\Models\Story;
use App\Models\User;
use Carbon\Carbon;
use FFMpeg\FFMpeg;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Pawlox\VideoThumbnail\VideoThumbnail;

class GameController extends Controller
{

    public function list(Request $request)
    {
        $type = $request->input('type');
        if($type) {
            $games = Game::where('type', $type)->first();
        } else {
            $games = Game::all();
        }
        return response()->json([
            'success' => true,
            'data' => $games,
        ]);
    }

}
