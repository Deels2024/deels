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
use Telegram\Bot\Api;
use Telegram\Bot\Exceptions\TelegramResponseException;
use Telegram\Bot\FileUpload\InputFile;

class SendTGChallengeModeration implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    private $challenge;
    private $old_moderator;

    public function __construct($challenge, $old_moderator = null)
    {
        $this->challenge = $challenge;
        $this->old_moderator = $old_moderator;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $reply_markup = null;
        $user_id = null;
        $moderators = get_option('tg_moderators', true);
        if($moderators) {
            $moderators = preg_split('/\r\n|\r|\n/', $moderators);

            if($this->old_moderator) {
                if (($key = array_search($this->old_moderator, $moderators)) !== false) {
                    unset($moderators[$key]);
                }
            }

            if(!empty($moderators)) {
                $rand_moderator_key = array_rand($moderators, 1);
                $user_id = $moderators[$rand_moderator_key];
            }

        }

        if(!$user_id) {
            return false;
        }

        CheckTGChallengeModeration::dispatch($this->challenge->id, $user_id)->delay(Carbon::now()->addMinutes(60));

        $text = "Требуется модерация челленджа #".$this->challenge->id."\n\n";
        $text .= "Описание: ".$this->challenge->description."\n";
        $text .= "Сумма: ".$this->challenge->amount."\n";
        $text .= "Автор: ".$this->challenge->user->name."(".$this->challenge->user->email.")\n";

        $telegram = new Api(env('TELEGRAM_BOT_TOKEN', '6942318602:AAF7h5O00sxeshn7USEDG7ad5cpbYP0VI_g'));
        try {

            $inline_button = array("text" => "❌ Отклонить", "callback_data" => "/challenge_moderation_decline_".$this->challenge->id);
            $inline_button2 = array("text" => "✅ Одобрить", "callback_data" => "/challenge_moderation_approve_".$this->challenge->id);
            $inline_keyboard = [[$inline_button, $inline_button2]];
            $keyboard = array("inline_keyboard" => $inline_keyboard);
            $reply_markup = json_encode($keyboard);
            $telegram->sendMessage(['chat_id' => $user_id, 'parse_mode' => 'html', 'reply_markup'=> null, 'text' => $text]);
            if($this->challenge->type == 'video') {
                $video = InputFile::create(public_path('/uploads/challenges/'.$this->challenge->media->slug_ext), 'challenge'.$this->challenge->id);
                $telegram->sendVideo(['chat_id' => $user_id, 'video' => $video, 'caption' => 'Челлендж #'.$this->challenge->id,'parse_mode' => 'html', 'duration' => 1,'reply_markup'=> $reply_markup, 'text' => $text]);
            } else {
                $photo = InputFile::create(public_path('/uploads/challenges/'.$this->challenge->media->slug_ext), 'challenge'.$this->challenge->id);
                $telegram->sendPhoto(['chat_id' => $user_id, 'photo' => $photo, 'caption' => 'Челлендж #'.$this->challenge->id,'parse_mode' => 'html', 'duration' => 1,'reply_markup'=> $reply_markup, 'text' => $text]);
            }

        } catch (TelegramResponseException $e) {
            Log::info('SendTGChallengeModeration ' . $e->getMessage());
        }

    }
}
