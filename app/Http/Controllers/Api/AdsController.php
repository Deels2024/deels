<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Http\Controllers\MediaController;
use App\Models\Abuse;
use App\Models\Challenge;
use App\Models\Likes;
use App\Models\Story;
use App\Models\User;
use Carbon\Carbon;
use FFMpeg\FFMpeg;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Pawlox\VideoThumbnail\VideoThumbnail;

class AdsController extends Controller
{

    public function ads_list(Request $request)
    {
        $title = 'Управление рекламой';

        $user = Auth::user();

        $stories = [];


        $story_id = $request->input('story_id');
        if ($user->is_admin() || $user->is_comment_admin()) {
            $stories_query = Story::query();

            if($story_id) {
                $stories_query->where('id', $story_id);
            }
            $stories = $stories_query->where('is_ad', true)->orderBy('id', 'desc')->paginate(12);
        }


        return view('admin.ads', compact('title', 'stories'));
    }

}
