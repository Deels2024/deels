<?php

namespace App\Observers;

use App\Jobs\User\AssistantAnswer;
use App\Models\Message;
use Cmgmyr\Messenger\Models\Participant;
use Illuminate\Support\Facades\Log;

class MessageObserver
{
    /**
     * Handle the Story "created" event.
     *
     * @param Message $message
     * @return void
     */
    public function creating(Message $message)
    {

    }

    public function created(Message $message)
    {

        if($message->user_id > 0) {
            if(in_array(0, $message->thread->users)) {
                AssistantAnswer::dispatch($message);
            }
        }

    }

    public function updating(Message $message)
    {

    }

    /**
     * Handle the Story "updated" event.
     *
     * @param \App\Models\Message $message
     * @return void
     */
    public function updated(Message $message)
    {

    }

    /**
     * Handle the Story "deleted" event.
     *
     * @param \App\Models\Message $message
     * @return void
     */
    public function deleted(Message $message)
    {

    }

    /**
     * Handle the Story "restored" event.
     *
     * @param \App\Models\Message $message
     * @return void
     */
    public function restored(Message $message)
    {
        //
    }

    /**
     * Handle the Story "force deleted" event.
     *
     * @param \App\Models\Message $message
     * @return void
     */
    public function forceDeleted(Message $message)
    {
        //
    }
}
