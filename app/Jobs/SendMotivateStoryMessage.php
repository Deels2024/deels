<?php

namespace App\Jobs;

use App\Helpers\AppHelper;
use App\Helpers\ChatGPTHelper;
use App\Models\Campaign;
use App\Models\Message;
use App\Models\User;
use Carbon\Carbon;
use Cmgmyr\Messenger\Models\Participant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendMotivateStoryMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    private $user_id;
    private $motivation_text;

    public function __construct($user_id, $motivation_text)
    {
        $this->user_id = $user_id;
        $this->motivation_text = $motivation_text;
        $this->queue = 'notifications';
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $recipient = User::find($this->user_id);
        $helper = new AppHelper();




        $chatgpt = new ChatGPTHelper();
        try {
            $campaings = Campaign::where('user_id', $this->user_id)->where('status', 1)->pluck('title')->toArray();
            if (count($campaings) > 0) {
                if($recipient) {
                    $motivation = $this->motivation_text;
                    $motivation = str_replace('Юзернейм', $recipient->name, $motivation);;
                    $button = [
                        'type' => 'action',
                        'action' => 'create_story',
                        'text' => 'Создать сторис',
                        'url' => route('stories.create')
                    ];
                    $helper->chat_notify($recipient,$motivation,$button);
                }

            }
        } catch (\Throwable $e) {
            Log::info('SendMotivateStoryMessage ' . $e->getMessage());
        }

    }
}
