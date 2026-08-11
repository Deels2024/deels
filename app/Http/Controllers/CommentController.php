<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Jobs\FireBaseEvent;
use App\Models\Campaign;
use App\Models\Comment;
use App\Models\User;
use App\Notifications\UserEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class CommentController extends Controller
{
    public function index()
    {
        $title = trans('app.comments');

        $user = Auth::user();

        if ($user->is_admin() || $user->is_comment_admin()) {
            $comments = Comment::query()->orderBy('id', 'desc')->paginate(50);
            if (request()->has('excel')) {
                return $this->collectionToExcel(Comment::query()->orderBy('id', 'desc')->get());
            }
        } else {
            // Get user specific comments
            $get_campaign_ids = $user->my_campaigns->pluck('id')->toArray();

            $comments = Comment::query()->whereIn('campaign_id', $get_campaign_ids)->orderBy('id', 'desc')->paginate(50);
        }

        return view('admin.comments', compact('title', 'comments'));
    }
    public function postCommentsApi(Request $request) {
        $rules['campaign_id'] = 'required';
        $rules['comment'] = 'required';
        $validator = validator($request->all(), $rules);
        if($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ]);
        }
        $user = User::find($request->user_id) ?? Auth::user() ?? auth()->user() ?? null;
        if(!$user) {
            return response()->json([
                'success' => false,
                'errors' => 'Пользователь не найден'
            ]);
        }
        $campaign = Campaign::find($request->campaign_id);
        if(!$campaign) {
            return response()->json([
                'success' => false,
                'errors' => 'Копилка не найдена'
            ]);
        }

        $data = [
            'user_id' => $user->id,
            'campaign_id' => $request->campaign_id,
            'comment_id' => 0,
            'author_name' => $user->name,
            'author_email' => $user->email,
            'author_ip' => null,
            'comment' => $request->comment,
            'approved' => 1,
        ];

        $post_comment = Comment::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Комментарий добавлен'
        ]);
    }

    public function comments_list($id) {

        $campaign = Campaign::find($id);

        if(!$campaign) {
            return response()->json([
                'success' => false,
                'errors' => 'Копилка не найдена'
            ]);
        }

        $comments = \App\Models\Comment::where('campaign_id', $id)->where('approved', true)->paginate(20);

        $data = [];

        foreach ($comments as $comment) {
            $data[] = $comment;
        }

        return response()->json([
            'success' => true,
            'data' => $data,
            'current_page' => $comments->currentPage(),
            'total_pages' => $comments->lastPage(),
        ]);

    }

    public function postComments(Request $request, $id = null)
    {
        $user_id = 0;

        $author_name = $request->author_name;
        $author_email = $request->author_email;
        if (!Auth::check()) {
            $rules['author_name'] = 'required';
            $rules['author_email'] = 'required';
            $this->validate($request, $rules);
        } else {
            $user = Auth::user();
            $user_id = $user->id;
            $author_name = $user->name;
            $author_email = $user->email;
        }


        $ip = $request->ip();
        $comment_id = $request->comment_id;
        if (!$comment_id) {
            $comment_id = 0;
        }

        // Auto approve if this ad owner
        $approved = 0;
        if (Auth::check()) {
            $user_id = Auth::user()->id;
            $ad = Campaign::find($id);
            if ($user_id == $ad->user_id) {
                $approved = 1;
            }
        } else {
            $request->session()->flash('success', trans('app.comment_posted'));
        }

        $data = [
            'user_id' => $user_id,
            'campaign_id' => $id,
            'comment_id' => $comment_id,
            'author_name' => $author_name,
            'author_email' => $author_email,
            'author_ip' => $ip,
            'comment' => $request->comment,
            'approved' => $approved,
        ];

        // If it reply, make it approve
        if ($comment_id) {
            $data['approved'] = 1;
        }
        $post_comment = Comment::create($data);

        $back_url = URL::previous().'?s=y#comment-'.$post_comment->id;

        return redirect($back_url);
    }

    public function commentAction(Request $request)
    {

        $user = Auth::user();

        // Preventing unauthorised action
        $comment = Comment::find($request->comment_id);
        $comment_ad = Campaign::find($comment->campaign_id);

        if (!$user->is_admin() && !$user->is_comment_admin()) {
            return ['success' => false];
        }

        switch ($request->action) {
            case 'approve':
                $comment->approved = 1;
                $comment->save();
                if($comment->campaign) {
                    if($comment->campaign->user) {
                        $text = 'Вам оставили комментарий на копилку №' . $comment->campaign->id . '.<br>Перейдите в личный кабинет на <a href="' . url('/') . '">deels.ru</a>,чтобы его посмотреть';
                        $comment->campaign->user->notify(new UserEmail('Вам оставили комментарий на копилку №' . $comment->campaign->id, $text));
                        FireBaseEvent::dispatch( $comment->campaign->user->id, 'Посмотрите новый комментарий к вашей копилке!', $comment->campaign->id, 'campaign');
                    }

                }
                break;

            case 'trash':
                $comment->delete();
                break;
        }

        return ['success' => 1];
    }
}
