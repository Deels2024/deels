<?php

declare(strict_types=1);

namespace App\Services\Messages;

use App\Models\Message;
use App\Models\Thread;
use App\Models\User;
use Carbon\Carbon;
use Cmgmyr\Messenger\Models\Participant;
use Illuminate\Support\Facades\DB;

class MessageThreadService
{
    private const ROBOT_USER_ID = 0;

    public function __construct(private MessageTextFormatter $textFormatter)
    {
    }

    public function findThreadForDialog($threadId, int $userId, $senderId): ?Thread
    {
        $participant = Participant::select('thread_id')
            ->with('thread')
            ->whereIn('user_id', [$userId, $senderId])
            ->groupBy('thread_id')
            ->havingRaw('COUNT(DISTINCT user_id) = 2')
            ->first();

        if ($participant) {
            return $participant->thread;
        }

        return Thread::find($threadId);
    }

    public function threadForUserQuery(int $userId)
    {
        return Thread::query()->where(function ($query) use ($userId): void {
            $query->whereJsonContains('users', $userId)
                ->orWhereJsonContains('users', (string) $userId);
        });
    }

    public function latestMessagesByThread(array $threadIds)
    {
        if (empty($threadIds)) {
            return collect();
        }

        $latestMessageIds = Message::query()
            ->select(DB::raw('MAX(id) as id'))
            ->whereIn('thread_id', $threadIds)
            ->groupBy('thread_id')
            ->pluck('id')
            ->filter()
            ->all();

        return Message::whereIn('id', $latestMessageIds)
            ->get()
            ->keyBy('thread_id');
    }

    public function formatMessagesByDate($messages, int $userId, bool $ascending): array
    {
        $messagesData = [];

        foreach ($messages->groupBy(function (Message $message) {
            return Carbon::parse($message->created_at)->format('d.m.Y');
        }) as $date => $dateMessages) {
            foreach ($dateMessages as $message) {
                $messagesData[$date][] = [
                    'user' => [
                        'id' => $message->user->id,
                        'avatar' => $message->user->avatar_url,
                        'name' => $message->user->name,
                    ],
                    'message' => $this->textFormatter->plainText($message->body),
                    'my_message' => $message->user_id == $userId,
                    'created_at' => Carbon::parse($message->created_at)->format('d.m.Y H:i:s'),
                ];
            }

            if ($ascending) {
                $messagesData[$date] = array_reverse($messagesData[$date]);
            }
        }

        return $messagesData;
    }

    public function threadUser(Thread $thread, int $userId): ?User
    {
        if (in_array(self::ROBOT_USER_ID, $thread->users ?: [], true)) {
            $user = new User();
            $user->id = self::ROBOT_USER_ID;
            $user->name = 'DEELS';
            $user->avatar = '/default_avatars/robot.jpeg';

            return $user;
        }

        return $thread->users->firstWhere('id', '!=', $userId)
            ?: $thread->users->firstWhere('id', $userId);
    }

    public function isThreadUnread(Thread $thread, ?Participant $participant): bool
    {
        if (!$participant) {
            return false;
        }

        if ($participant->last_read === null) {
            return true;
        }

        return $thread->updated_at->gt($participant->last_read);
    }
}
