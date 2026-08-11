<?php

namespace App\Http\Controllers\Api;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Thread;
use App\Models\User;
use App\Services\Messages\MessageTextFormatter;
use App\Services\Messages\MessageThreadService;
use Carbon\Carbon;
use Cmgmyr\Messenger\Models\Participant;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class MessagesController extends Controller
{
    private const MESSAGES_PER_PAGE = 20;

    /**
     * Shows a message thread.
     *
     * @param $id
     * @return mixed
     */
    public function show(Request $request)
    {
        $thread_id = $request->input('thread_id');
        $sender_id = $request->input('user_id');
        $order_by = $request->input('order_by');
        $currentUser = $request->user();
        $userId = $currentUser->id;

        if(!$thread_id) {
            return response()->json([
                'success' => false,
                'error' => 'Переписка не существует'
            ]);
        }

        $thread = app(MessageThreadService::class)->findThreadForDialog($thread_id, $userId, $sender_id);

        if (!$thread) {
            return response()->json([
                'success' => false,
                'error' => 'Переписка не существует'
            ]);
        }

        $users = $thread->users()->where('users.id', '!=', $userId)->get();
        $blocked = $users->contains(function (User $threadUser) use ($currentUser) {
            return $currentUser->blockedBy($threadUser->id);
        });

        $thread->markAsRead($userId);
        $messages = $this->getThreadMessagesPage($thread);
        $messagesData = app(MessageThreadService::class)->formatMessagesByDate($messages->getCollection(), $userId, strtolower((string) $order_by) === 'asc');

        return response()->json([
            'success' => true,
            'thread_id' => $thread->id,
            'data' => $messagesData,
            'is_blocked' => $blocked,
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
            return response()->json([
                'success' => false,
                'error' => 'Пользователь ограничил возможность писать сообщение не из списка своих подписок.',
                'errors' => 'Пользователь ограничил возможность писать сообщение не из списка своих подписок.',
            ], 403);
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
        $thread = Thread::create([
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
    public function send_message(Request $request)
    {
        $thread_id = $request->input('thread_id');
        $currentUser = $request->user();
        $userId = $currentUser->id;
        $message = $request->input('message');
        $recipients = $request->input('user_id');
        $user_recipient = null;

        if($recipients) {
            $user_recipient = User::find($recipients);
            if(!$user_recipient) {
                return response()->json([
                    'success' => false,
                    'errors' => 'Пользователь не найден',
                ]);
            }

            if($currentUser->blockedBy($user_recipient->id)) {
                return response()->json([
                    'success' => false,
                    'errors' => 'Вы не можете писать этому пользователю',
                ]);
            }
        }

        try {
            $thread = Thread::findOrFail($thread_id);
        } catch (ModelNotFoundException $e) {
            if (!$user_recipient || !$user_recipient->canReceiveFirstMessageFrom($currentUser)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Пользователь ограничил возможность писать сообщение не из списка своих подписок.',
                    'errors' => 'Пользователь ограничил возможность писать сообщение не из списка своих подписок.',
                ], 403);
            }
            $thread = $this->store($userId, $recipients, $message);
        }

        $thread->activateAllParticipants();

        $new_message = Message::create([
            'thread_id' => $thread->id,
            'user_id' => $userId,
            'body' => $message,
        ]);

        $participant = Participant::firstOrCreate([
            'thread_id' => $thread->id,
            'user_id' => $userId,
        ]);
        $participant->last_read = new Carbon();
        $participant->save();

        if ($request->has('user_id')) {
            $thread->addParticipant($recipients);
        }

        $helper = new AppHelper();
        $helper->firebase_notify($recipients, $new_message);

        return response()->json([
            'success' => true,
            'thread_id' => $thread->id,
        ]);
    }

    public function get_list(Request $request)
    {
        $userId = $request->user()->id;
        $query = $request->input('query');

        $threadService = app(MessageThreadService::class);
        $textFormatter = app(MessageTextFormatter::class);
        $threadsQuery = $threadService->threadForUserQuery($userId)
            ->with(['users', 'participants'])
            ->withCount([
                'messages as unread_count' => function ($q) use ($userId): void {
                    $q->where('user_id', '!=', $userId)
                        ->whereExists(function ($subQuery) use ($userId): void {
                            $subQuery->select(\DB::raw(1))
                                ->from('participants')
                                ->whereColumn('participants.thread_id', 'messages.thread_id')
                                ->where('participants.user_id', $userId)
                                ->where(function ($participantQuery): void {
                                    $participantQuery->whereNull('participants.last_read')
                                        ->orWhereColumn('messages.updated_at', '>', 'participants.last_read');
                                });
                        });
                }
            ])
            ->latest('updated_at');

        if ($query) {
            $threadsQuery->whereHas('messages', function ($q) use ($query): void {
                $q->where('body', 'like', '%' . $query . '%');
            });
        }

        $threads = $threadsQuery->get();

        if ($threads->isEmpty()) {
            return response()->json([
                'success' => false,
                'error' => $query ? 'Чаты не найдены' : 'Вы никому не писали'
            ]);
        }

        $data_threads = [];
        $latestMessages = $threadService->latestMessagesByThread($threads->pluck('id')->all());

        foreach ($threads as $thread) {
            $lastMessage = $latestMessages->get($thread->id);
            $participant = $thread->participants->firstWhere('user_id', $userId);
            $threadUser = $threadService->threadUser($thread, $userId);

            $thread_item = [];
            $thread_item['id'] = $thread->id;
            $thread_item['unread_count'] = (int) $thread->unread_count;
            $thread_item['unread'] = $threadService->isThreadUnread($thread, $participant);
            $thread_item['last_message'] = $textFormatter->plainText($lastMessage->body ?? '');
            $thread_item['date'] = $lastMessage ? Carbon::parse($lastMessage->created_at)->format('d.m.Y H:i') : '';
            $thread_item['user']['id'] = $threadUser ? $threadUser->id : '';
            $thread_item['user']['avatar'] = $threadUser ? url($threadUser->avatar()) : '';
            $thread_item['user']['name'] = $threadUser ? $threadUser->name : '';
            $data_threads[] = $thread_item;
        }

        return response()->json([
            'success' => true,
            'data' => $data_threads,
        ]);

    }

    public function mark_as_read(Request $request)
    {
        $userId = $request->user()->id;
        $thread_id = $request->input('thread_id');

        $thread = app(MessageThreadService::class)->threadForUserQuery($userId)->where('id', $thread_id)->latest('updated_at')->first();

        if ($thread) {
            $thread->markAsRead($userId);
        } else {
            return response()->json([
                'success' => false,
                'error' => 'Чат не найден'
            ]);
        }

        return response()->json([
            'success' => true,
        ]);

    }

    public function delete_thread(Request $request) {
        $userId = $request->user()->id;
        $thread_id = $request->input('thread_id');

        $thread = app(MessageThreadService::class)->threadForUserQuery($userId)->where('id', $thread_id)->latest('updated_at')->first();
        if ($thread) {
            $thread->delete();
            Message::where('thread_id', $thread_id)->delete();
        } else {
            return response()->json([
                'success' => false,
                'error' => 'Чат не найден'
            ]);
        }

        return response()->json([
            'success' => true,
        ]);
    }

    private function getThreadMessagesPage(Thread $thread)
    {
        return $thread->messages()
            ->with('user')
            ->orderBy('created_at', 'DESC')
            ->paginate(self::MESSAGES_PER_PAGE);
    }

}
