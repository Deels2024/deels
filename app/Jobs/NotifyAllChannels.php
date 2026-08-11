<?php

namespace App\Jobs;

use App\Helpers\AppHelper;
use App\Models\User;
use App\Notifications\UserEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;

class NotifyAllChannels implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    private $user_id;
    private $message;
    private $title;
    private $clear_device;

    public function __construct($user_id, $message, $title = 'Новый статус модерации', $clear_device = false)
    {
        $this->user_id = $user_id;
        $this->message = $message;
        $this->title = $title;
        $this->clear_device = $clear_device;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $user_id = $this->user_id;
        $user = User::find($user_id);
        $message = $this->message;
        FireBaseEvent::dispatch($user_id, $message);
        if($user) {
            $helper = new AppHelper();
            $helper->chat_notify($user,$message);
            if($user->email) {
                $user->notify(new UserEmail($this->title, $message));
            }
            if($this->clear_device) {
                $user->device_key = null;
                $user->saveQuietly();
            }
        }
    }
}
