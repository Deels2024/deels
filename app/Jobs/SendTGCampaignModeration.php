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

class SendTGCampaignModeration implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    private $campaign;

    public function __construct($campaign)
    {
        $this->campaign = $campaign;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $reply_markup = null;
        $moderators = get_option('tg_moderators', true);
        $moderators = preg_split('/\r\n|\r|\n/', $moderators);

        $rand_moderator_key = array_rand($moderators, 1);
        $user_id = $moderators[$rand_moderator_key];

        if(!$user_id) {
            return false;
        }

        $text = "Требуется модерация копилки #".$this->campaign->id."\n\n";
        $text .= "Название: ".$this->campaign->title."\n";
        $text .= "Категория: ".$this->campaign->get_category->category_name."\n";
        $text .= "Цель: ".$this->campaign->goal."\n";
        $text .= "Видео: ".$this->campaign->video ?? 'Нет'."\n";
        $text .= "Описание: \n".htmlentities($this->campaign->description)."\n";
        $text .= "Автор: ".$this->campaign->user->name."(".$this->campaign->user->email.")\n";

        $telegram = new Api(env('TELEGRAM_BOT_TOKEN', '6942318602:AAF7h5O00sxeshn7USEDG7ad5cpbYP0VI_g'));
        try {

            $inline_button = array("text" => "❌ Отклонить", "callback_data" => "/campaign_moderation_decline_".$this->campaign->id);
            $inline_button2 = array("text" => "✅ Одобрить", "callback_data" => "/campaign_moderation_approve_".$this->campaign->id);
            $inline_keyboard = [[$inline_button, $inline_button2]];
            $keyboard = array("inline_keyboard" => $inline_keyboard);
            $reply_markup = json_encode($keyboard);
            $telegram->sendMessage(['chat_id' => $user_id, 'parse_mode' => 'html', 'reply_markup'=> null, 'text' => $text]);
            $image = $this->campaign->feature_media->slug_ext;
            $photo = InputFile::create(public_path('uploads/images/'.$image), 'campaign'.$this->campaign->id);
            $telegram->sendPhoto(['chat_id' => $user_id, 'photo' => $photo, 'caption' => 'Копилка #'.$this->campaign->id,'parse_mode' => 'html', 'duration' => 1,'reply_markup'=> $reply_markup, 'text' => $text]);

        } catch (TelegramResponseException $e) {
            Log::info('SendTGCampaignModeration ' . $e->getMessage());
        }

    }
}
