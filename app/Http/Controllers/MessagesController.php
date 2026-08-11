<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Jobs\SendTGPMNotification;
use App\Models\User;
use Carbon\Carbon;
use App\Models\Message;
use Cmgmyr\Messenger\Models\Participant;
use App\Models\Thread;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;

class MessagesController extends Controller
{
    private const MESSAGES_PER_PAGE = 10;

    /**
     * Show all of the message threads to the user.
     *
     * @return mixed
     */
    public function index()
    {
        // All threads, ignore deleted/archived participants
//        $threads = Thread::getAllLatest()->get();

        // All threads that user is participating in
        $threads = \App\Models\Thread::forUser(Auth::id())->latest('updated_at')->get();

        // All threads that user is participating in, with new messages
//         $threads = Thread::forUserWithNewMessages(Auth::id())->latest('updated_at')->get();

        return view('messenger.index', compact('threads'));
    }

    /**
     * Shows a message thread.
     *
     * @param $id
     * @return mixed
     */
    public function show(\Illuminate\Http\Request $request)
    {
        $thread_id = $request->input('thread');
        $sender_id = $request->input('sender_id');
        $userId = $request->input('user_id');
        $messages_only = $request->input('messages_only');

        $thread = null;
        $participant = Participant::select('thread_id')
            ->whereIn('user_id', [$userId, $sender_id])
            ->groupBy('thread_id')
            ->havingRaw('COUNT(DISTINCT user_id) = 2')
            ->first();
        if ($participant) {
            $thread = $participant->thread;
        }

        if(!$thread) {
            try {
                $thread = \App\Models\Thread::findOrFail($thread_id);
            } catch (ModelNotFoundException $e) {
                $user = User::find($userId);
                $view = view('messenger.create', compact('user'))->render();
                return response()->json([
                    'success' => true,
                    'view' => $view
                ]);
            }
        }


        $users = $thread->users()->where('users.id', '!=', $userId)->get();

        $thread->markAsRead($userId);

        $messages = $this->getThreadMessagesPage($thread, (int) $request->input('page', 1));
        $messages_dates = $this->groupMessagesByDate($messages);

        $view = view('messenger.show', compact('thread', 'users', 'messages', 'messages_dates'))->render();

        if($messages_only) {
            $view = view('messenger.partials.messages', compact('messages_dates'))->render();
        }

        return response()->json([
            'success' => true,
            'view' => $view,
            'thread' => $thread->id ?? null,
            'current_page' => $messages->currentPage(),
            'total_pages' => $messages->lastPage(),
        ]);
    }

    /**
     * Creates a new message thread.
     *
     * @return mixed
     */
    public function create($id)
    {
        $thread = null;
        $user = User::find($id);
        $sender = User::find(Auth::id());
        $user_id = $user->id;
        $participant = Participant::select('thread_id')
            ->whereIn('user_id', [$user->id, $sender->id])
            ->groupBy('thread_id')
            ->havingRaw('COUNT(DISTINCT user_id) = 2')
            ->first();
        if ($participant) {
            $thread = $participant->thread;
        }

        if ($thread) {
            $users = $thread->users()->where('users.id', '!=', $sender->id)->get();
            $thread->markAsRead($sender->id);
            return view('messenger.show', compact('thread', 'users'));
        }

        if (!$user->canReceiveFirstMessageFrom($sender)) {
            return back()->with('error', 'Пользователь ограничил возможность писать сообщение не из списка своих подписок.');
        }


        return view('messenger.create', compact('user'));
    }

    /**
     * Stores a new message thread.
     *
     * @return mixed
     */
    public function store($sender_id, $recipients, $message)
    {
        $input = Request::all();

        $thread = \App\Models\Thread::create([
            'subject' => Carbon::now(),
            'users' => [$sender_id,$recipients]
        ]);

        return $thread;
    }

    /**
     * Adds a new message to a current thread.
     *
     * @param $id
     * @return mixed
     */
    public function send_message(\Illuminate\Http\Request $request)
    {
        $thread_id = $request->input('thread_id');
        $userId = $request->input('user_id');
        $message = $request->input('message');
        $recipients = $request->input('recipients');
        try {
            $thread = \App\Models\Thread::findOrFail($thread_id);
        } catch (ModelNotFoundException $e) {
            $recipient = User::find($recipients);
            $sender = User::find($userId);
            if (!$recipient || !$sender || !$recipient->canReceiveFirstMessageFrom($sender)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Пользователь ограничил возможность писать сообщение не из списка своих подписок.',
                ], 403);
            }
            $thread = $this->store($userId, $recipients, $message);
        }

        $thread->activateAllParticipants();

        // Message
        $new_message = Message::create([
            'thread_id' => $thread->id,
            'user_id' => Auth::id(),
            'body' => $message,
        ]);


        // Add replier as a participant
        $participant = Participant::firstOrCreate([
            'thread_id' => $thread->id,
            'user_id' => Auth::id(),
        ]);
        $participant->last_read = new Carbon();
        $participant->save();

        $helper = new AppHelper();
        foreach ($thread->users as $thread_user) {
            if($thread_user != Auth::id()) {

                $helper->firebase_notify($thread_user, $new_message);
                try {
                    $thread_user_model = User::find($thread_user);
                    if($thread_user_model) {
                        $text = Auth::user()->username.' написал вам личное сообщение';
                        $url = url('/');
                        SendTGPMNotification::dispatch($thread_user_model, $text, $url);
                    }

                } catch (\Throwable $e) {

                }
            }
        }

        // Recipients
        if (Request::has('recipients')) {
            $thread->addParticipant(Request::input('recipients'));
        }

        $view = view('messenger.partials.message', ['message' => $new_message])->render();
        return response()->json([
            'success' => true,
            'view' => $view,
            'thread_id' => $thread->id,
        ]);
    }

    public function get_list(\Illuminate\Http\Request $request) {
        $userId = $request->input('user_id');
        $query = $request->input('query');

        if($query) {
            $threads = \App\Models\Thread::forUser($userId)->latest('updated_at')->whereHas('messages', function($q) use($query): void {
                $q->where('body', 'like', '%'.$query.'%');
            })->get();
        } else {
            $threads = \App\Models\Thread::forUser($userId)->latest('updated_at')->get();
        }


        $view = 'Вы никому не писали';
        if($query) {
            $view = 'Чаты не найдены';
        }
        if(count($threads) > 0) {
            $view = '';
        }
        foreach ($threads as $thread) {
            $view .= view('messenger.partials.thread', ['thread' => $thread])->render();
        }
        return response()->json([
            'success' => true,
            'view' => $view,
        ]);

    }

    private function getThreadMessagesPage(Thread $thread, int $page)
    {
        return $thread->messages()
            ->orderBy('created_at', 'DESC')
            ->paginate(self::MESSAGES_PER_PAGE, ['*'], 'page', max($page, 1));
    }

    private function groupMessagesByDate($messages)
    {
        return $messages
            ->getCollection()
            ->sortBy('created_at')
            ->groupBy(function ($message) {
                return Carbon::parse($message->created_at)->format('d.m.Y');
            })
            ->sortBy(function ($messages, $date) {
                return Carbon::createFromFormat('d.m.Y', $date);
            });
    }
}
