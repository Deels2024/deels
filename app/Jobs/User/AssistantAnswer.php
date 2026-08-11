<?php

namespace App\Jobs\User;

use App\Helpers\AppHelper;
use App\Helpers\ChatGPTHelper;
use App\Jobs\FireBaseEvent;
use App\Models\Campaign;
use App\Models\Challenge;
use App\Models\Message;
use App\Models\Story;
use App\Models\User;
use Carbon\Carbon;
use Cmgmyr\Messenger\Models\Participant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Api;
use Telegram\Bot\Exceptions\TelegramResponseException;
use Telegram\Bot\FileUpload\InputFile;

class AssistantAnswer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    private $message;

    public function __construct($message)
    {
        $this->message = $message;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $message_data = json_decode($this->message);
        $answer = 'Простите, я бы рад пообщаться, но пока не могу :)';
        $message_participant = Participant::where('thread_id', $message_data->thread_id)->where('user_id', '!=', $message_data->user_id)->first();
        $chatgpt = new ChatGPTHelper();
        try {
            $chatgpt_response = $chatgpt->assistant_text($message_data->body);
            $answer = $chatgpt_response['message'];
        } catch (\Throwable $e) {
            Log::info(['AssistantAnswer error '.$e->getMessage(),$chatgpt_response]);
        }

        if($message_participant->user_id == 0) {
            $new_message = Message::create([
                'thread_id' => $message_data->thread_id,
                'user_id' => 0,
                'body' => $answer,
            ]);
        }
    }
}
