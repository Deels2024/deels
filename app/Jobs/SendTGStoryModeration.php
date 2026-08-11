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

class SendTGStoryModeration implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    private $story;
    private $old_moderator;

    public function __construct($story, $old_moderator = null)
    {
        $this->story = $story;
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

        CheckTGStoryModeration::dispatch($this->story->id, $user_id)->delay(Carbon::now()->addMinutes(60));

        $paid = $this->story->paid ? 'Да ('.$this->story->amount.' '.trans_choice('numbers.coins', intval($this->story->amount) ?? 0).')' : 'Нет';
        $text = "Требуется модерация сторис #".$this->story->id."\n\n";
        $text .= "Описание: ".$this->story->description."\n";
        if($this->story->challenge_id) {
            $text .= "Челлендж: ".$this->story->challenge->title."\n";
        }
        $text .= "Платная: ".$paid."\n";
        $text .= "Автор: ".$this->story->user->name."(".$this->story->user->email.")\n";

        $telegram = new Api(env('TELEGRAM_BOT_TOKEN', '6942318602:AAF7h5O00sxeshn7USEDG7ad5cpbYP0VI_g'));
        try {

            $inline_button = array("text" => "❌ Отклонить", "callback_data" => "/story_moderation_decline_".$this->story->id);
            $inline_button2 = array("text" => "✅ Одобрить", "callback_data" => "/story_moderation_approve_".$this->story->id);
            $inline_keyboard = [[$inline_button, $inline_button2]];
            $keyboard = array("inline_keyboard" => $inline_keyboard);
            $reply_markup = json_encode($keyboard);
            $telegram->sendMessage(['chat_id' => $user_id, 'parse_mode' => 'html', 'reply_markup'=> null, 'text' => $text]);
            try {
                if($this->story->type == 'video') {
                    $video = InputFile::create(public_path('/uploads/stories/'.$this->story->media->slug_ext), 'story'.$this->story->id);
                    $telegram->sendVideo(['chat_id' => $user_id, 'video' => $video, 'caption' => 'Сторис #'.$this->story->id,'parse_mode' => 'html', 'duration' => 1,'reply_markup'=> $reply_markup, 'text' => $text]);
                } else {
                    $photo = InputFile::create(public_path('/uploads/stories/'.$this->story->media->slug_ext), 'story'.$this->story->id);
                    $telegram->sendPhoto(['chat_id' => $user_id, 'photo' => $photo, 'caption' => 'Сторис #'.$this->story->id,'parse_mode' => 'html', 'duration' => 1,'reply_markup'=> $reply_markup, 'text' => $text]);
                }
            } catch (\Throwable $e) {
                $telegram->sendMessage(['chat_id' => $user_id, 'parse_mode' => 'html', 'reply_markup'=> $reply_markup, 'text' => 'Действие']);
            }
        } catch (TelegramResponseException $e) {
            Log::info('SendTGStoryModeration ' . $e->getMessage());
        }

    }
}
