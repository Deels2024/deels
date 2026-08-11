<?php

namespace App\Http\Controllers;

use App\Jobs\FireBaseEvent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FollowController extends Controller
{
    public function follow_toggle(Request $request)
    {
//        Log::info($request->input());
        $user_id = Auth::user()->id ?? auth()->user()->id ?? $request->input('user_id');
        $follow_id = $request->input('follow_id');
        $user1 = User::find($user_id);
        $user2 = User::find($follow_id);
        if(!$user2 || !$user1) {
            return response()->json([
                'success' => false,
                'error' => 'Пользователь отсутствует',
            ]);
        }

        if($user_id == $follow_id) {
            return response()->json([
                'success' => false,
                'error' => 'Вы не можете подписаться на себя',
            ]);
        }

        if ($user1->isFollowing($user2)) {
            $user1->unfollow($user2);
            return response()->json([
                'success' => true,
                'message' => 'Вы отписались',
                'count' => $user1->followings->count()
            ]);
        } else {
            $user1->follow($user2);
            $user2->acceptFollowRequestFrom($user1);
            FireBaseEvent::dispatch( $user2->id, 'У вас появился новый подписчик', $user2->id, 'profile');
            return response()->json([
                'success' => true,
                'message' => 'Вы подписались',
                'count' => $user1->followings->count()
            ]);
        }
    }

    public function follow_list(Request $request){
        $title = 'Друзья';
        $user = Auth::user() ?? auth()->user() ?? null;
        if($user) {
            $users = [];
            $followers = $user->followings()->with('followable')->get();

            if (!request()->wantsJson()) {
                $friend_ids = DB::table('followables')
                    ->where('followable_id', $user->id)
                    ->pluck('user_id')
                    ->toArray();
                $followers = $user->followings()
                    ->with('followable')
                    ->whereNotIn('followable_id', $friend_ids)
                    ->get();
            }

            foreach ($followers as $follower) {
                $user = [];
                $user['id'] = $follower->followable->id;
                $user['username'] = $follower->followable->username;
                $user['avatar'] = url($follower->followable->avatar());
                $user['followed_at'] = \Carbon\Carbon::parse($follower->accepted_at)->format('d.m.Y H:i');
                $users[] = $user;
            }
        } else {
            return response()->json([
                'success' => false,
                'error' => 'Вы не авторизованы',
            ]);
        }

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'followers' => $users
            ]);
        }
        $mode = 'followers';
        return view('admin.followers', compact('title', 'followers', 'mode'));
    }

    public function following_list(Request $request){
        $title = 'Друзья';
        $user = Auth::user() ?? auth()->user() ?? null;
        if($user) {
            $users = [];

            $followers_table_query = DB::table('followables')->where('followable_id', $user->id);

            if (!request()->wantsJson()) {
                $followings_ids = $user->followings()->pluck('followable_id')->toArray();
                $followers_table_query->whereNotIn('user_id', $followings_ids);
            }

            $followers_table = $followers_table_query->get();
            $followers_ids = collect($followers_table);
            $followers = User::whereIn('id', $followers_ids->pluck('user_id')->toArray())->get();
            foreach ($followers as $follower) {
                $user = [];
                $user['id'] = $follower->id;
                $user['username'] = $follower->username;
                $user['avatar'] = url($follower->avatar());
                $user['followed_at'] = \Carbon\Carbon::parse($followers_ids->where('user_id', $follower->id)->first()->accepted_at)->format('d.m.Y H:i');
                $users[] = $user;
                $follower->followable = $follower;
                $follower->accepted_at = $followers_ids->where('user_id', $follower->id)->first()->accepted_at;
            }
        } else {
            return response()->json([
                'success' => false,
                'error' => 'Вы не авторизованы',
            ]);
        }

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'followers' => $users
            ]);
        }

        $mode = 'followings';
        return view('admin.followers', compact('title', 'followers', 'mode'));
    }

    public function friends_list(Request $request){
        $title = 'Друзья';
        $user = Auth::user() ?? auth()->user() ?? null;
        if($user) {
            $users = [];
            $followers_ids = DB::table('followables')
                ->where('followable_id', $user->id)
                ->pluck('user_id')
                ->toArray();
            $followers = $user->followings()
                ->with('followable')
                ->whereIn('followable_id', $followers_ids)
                ->get();
            foreach ($followers as $follower) {
                $item = [];
                $item['id'] = $follower->followable->id;
                $item['username'] = $follower->followable->username;
                $item['avatar'] = url($follower->followable->avatar());
                $users[] = $item;
            }
        } else {
            return response()->json([
                'success' => false,
                'error' => 'Вы не авторизованы',
            ]);
        }

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'followers' => $users
            ]);
        }

        $mode = 'friends';
        return view('admin.followers', compact('title', 'followers', 'mode'));
    }
}
