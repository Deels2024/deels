<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Story;
use App\Services\Stories\StoryAccountInfoService;
use App\Services\Stories\StoryAdminService;
use App\Services\Stories\StoryCommentService;
use App\Services\Stories\StoryDonationService;
use App\Services\Stories\StoryFileResponseService;
use App\Services\Stories\StoryGetService;
use App\Services\Stories\StoryPreviewService;
use App\Services\Stories\StoryReactionService;
use App\Services\Stories\StoryStoreService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StoryController extends Controller
{
    public function create(Request $request): Factory|View|Application
    {
        $title = 'Добавить сторис';
        $isUseful = $request->boolean('useful');

        return view('stories.create', compact('title', 'isUseful'));
    }

    public function get_file($id)
    {
        return app(StoryFileResponseService::class)->getFile($id);
    }

    public function get(Request $request, $id, $only_body = false, $donate = true)
    {
        return app(StoryGetService::class)->get($request, $id, (bool) $only_body, (bool) $donate);
    }

    public function getPreview(Request $request, $id)
    {
        return response()->json(app(StoryPreviewService::class)->preview($request, $id))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function donate(Request $request, $id)
    {
        return response()->json(app(StoryDonationService::class)->donate($request, $id, [$this, 'get']));
    }

    public function pay(Request $request, $id = null)
    {
        return response()->json(app(StoryDonationService::class)->pay($request, $id));

    }


    public function store(Request $request)
    {
        return response()->json(app(StoryStoreService::class)->store($request));

    }

    public function store_web(Request $request)
    {
        $result = app(StoryStoreService::class)->storeWeb($request);

        if ($result['type'] === 'json') {
            return response()->json($result['payload']);
        }

        if ($result['type'] === 'redirect') {
            return redirect()->route($result['route'])->with('success', $result['message']);
        }

        $response = back()->with('error', $result['message']);
        if ($result['with_input'] ?? false) {
            return $response->withInput();
        }

        return $response;

    }


    public function like(Request $request)
    {
        return response()->json(app(StoryReactionService::class)->like($request));
    }

    public function dislike(Request $request)
    {
        return response()->json(app(StoryReactionService::class)->dislike($request));
    }

    public function commentLike(Request $request)
    {
        return response()->json(app(StoryReactionService::class)->commentLike($request));
    }

    public function comment(Request $request)
    {
        return response()->json(app(StoryCommentService::class)->create($request));
    }

    public function account_info($id = null, $only_data = false)
    {
        $userData = app(StoryAccountInfoService::class)->build($id);
        if (!$userData) {
            if($only_data) {
                return  null;
            }
            return response()->json([
                'success' => false,
                'error' => 'User ID ' . $id . ' not found'
            ]);
        }

        if ($only_data) {
            return $userData;
        }

        return response()->json([
            'success' => true,
            'data' => $userData
        ]);
    }

    public function user_stories(Request $request)
    {
        $user = request()->user();
        $type = $request->type;
        if($type == 'participant') {
            $stories = $user->stories()->whereNotNull('challenge_id')->notUseful()->orderBy('created_at', 'DESC')->get();
        } else {
            $stories = $user->stories()->orderBy('created_at', 'DESC')->get();
        }

        return view('dashboard.stories.stories_index', compact('user', 'stories'));
    }

    public function stories_list(Request $request)
    {
        $data = app(StoryAdminService::class)->storiesList($request, Auth::user(), Auth::id() ?? auth()->id());

        return view('admin.stories', $data);
    }

    public function challenges_stories_list(Request $request)
    {
        $data = app(StoryAdminService::class)->challengeStoriesList($request, Auth::user(), Auth::id() ?? auth()->id());

        return view('admin.challenges_stories', $data);
    }



    public function likes_list(Request $request)
    {
        $user_id = Auth::user()->id ?? auth()->user()->id ?? null;
        $data = app(StoryAdminService::class)->likesList($request, $user_id);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'comments' => $data['comments'],
                'campaigns' => $data['campaigns'],
                'stories' => $data['stories'],
            ]);
        }

        return view('admin.likes', $data);
    }

    public function confirm(Request $request)
    {
        return app(StoryAdminService::class)->confirm($request, Auth::user());
    }

    public function admin_challenge_stories_confirm(Request $request)
    {
        return app(StoryAdminService::class)->confirmChallengeStory($request, Auth::user());
    }



    public function remove(Request $request)
    {
        return app(StoryAdminService::class)->remove($request, Auth::user() ?? auth()->user() ?? null);
    }

    public function excludeUseful(Request $request)
    {
        $story = Story::withoutGlobalScopes()->findOrFail((int) $request->input('story_id'));
        $user = Auth::user();
        $contest = $story->challenge ?: $story->battle;

        abort_unless(
            $user && $story->is_useful && $contest
            && (int) $contest->user_id === (int) $user->id,
            403
        );

        $story->challenge_id = null;
        $story->battle_id = null;
        $story->is_useful = false;
        $story->save();

        return response()->json(['success' => true]);
    }

    public function repost(Request $request)
    {
        return app(StoryAdminService::class)->repost($request, Auth::user());
    }

    public function add_likes(Request $request){
        app(StoryAdminService::class)->addLikes($request, Auth::user());

        return redirect()->back()->with('success', 'Это успех!');
    }

    public function admin_stories_likes(Request $request){
        return view('admin.stories_likes', app(StoryAdminService::class)->storiesLikes($request));
    }

    public function admin_stories_dislikes(Request $request){
        return view('admin.stories_dislikes', app(StoryAdminService::class)->storiesDislikes($request));
    }
}
