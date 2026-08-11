<?php

namespace App\Jobs;

use App\Helpers\AppHelper;
use App\Helpers\ChatGPTHelper;
use App\Helpers\TgHelper;
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
use Telegram\Bot\Api;
use Telegram\Bot\Exceptions\TelegramResponseException;
use Telegram\Bot\FileUpload\InputFile;

class SendTGPMNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, TgHelper;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    private $user;
    private $text;
    private $url;

    public function __construct($user, $text, $url = null)
    {
        $this->user = $user;
        $this->text = $text;
        $this->url = $url;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if($this->user->telegram_notify) {
            $this->sendTgMessage($this->text, $this->user->telegram_id, $this->url);
        }

    }
}
