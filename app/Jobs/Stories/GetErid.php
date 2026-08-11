<?php

namespace App\Jobs\Stories;

use App\Helpers\TgHelper;
use App\Models\Story;
use FFMpeg\FFMpeg;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Pawlox\VideoThumbnail\VideoThumbnail;
use Telegram\Bot\Api as TelegramApi;

class GetErid implements ShouldQueue
{
    use TgHelper;
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    private $story_id;

    public function __construct($story_id)
    {
        $this->story_id = $story_id;
    }


    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $story = Story::find($this->story_id);
        if(!$story) {
            return;
        }
        $ads_data = $story->ads_data;
        if(isset($ads_data['get_erid']) && $ads_data['get_erid']) {
            $description = 'Рекламодатель: '.$ads_data['advertiser'];
            if($story->description) {
                $description .= ' | '. $story->description;
            }
            $urls = ['https://deels.ru'];
            if (isset($ads_data['additional_link']) && $ads_data['additional_link']) {
                $additionalLink = $ads_data['additional_link'];
                if (filter_var($additionalLink, FILTER_VALIDATE_URL)) {
                    $urls[] = $additionalLink;
                }
            }
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.env('ORD_TOKEN'), // Замените на ваш токен
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])
                ->post(env('ORD_URL').'/api/v6/creative', [
                    'coBranding' => true,
                    'contractIds' => ["1"],
                    'creativeType' => "creative",
                    'description' => $description,
                    'form' => "video",
                    'id' => "story".$story->id,
                    'isSocial' => true,
                    'isSocialQuota' => true,
                    'kktuCodes' => ["30.15.1"],
                    'mediaData' => [
                        [
                            'mediaUrl' => $story->getFile(),
                            'mediaUrlFileType' => "video"
                        ]
                    ],
                    'isEntireRussiaRegion' => true,
                    'urls' => $urls
                ]);

            if ($response->successful()) {
                $data = $response->json();
                if(isset($data['erir_id'])) {
                    $ads_data['erid'] = $data['erir_id'];
                    $story->ads_data = $ads_data;
                    $story->saveQuietly();
                }
            } else {
                $error = $response->json();

                $telegram = new TelegramApi(env('TELEGRAM_BOT_TOKEN', '6942318602:AAF7h5O00sxeshn7USEDG7ad5cpbYP0VI_g'));
                $name = 'Ошибка GetErid ' . env('APP_NAME');

                $message = self::jsonToText($error);

                $telegram->sendMessage([
                    'chat_id' => env('TELEGRAM_CHAT_ID', 190036322),
                    'text' => "<b>{$name}</b>\n{$message}",
                    'parse_mode' => 'HTML',
                ]);
            }
        }

    }


    public static function jsonToText($message)
    {
        $message = json_encode($message, JSON_UNESCAPED_UNICODE);
        $message = str_replace(['<', '>'], ['&lt;', '&gt;'], $message);
        $message = self::traitMessage(json_decode($message, true));

        if (mb_strlen($message) > 4000) {
            $message = mb_substr($message, 0, 4000) . "...";
        }

        return $message;
    }

    public static function traitMessage($arr)
    {
        $text = '';

        foreach ($arr as $key => $value) {
            if (is_array($value) == false) {
                $text .= "{$key}: {$value}\n";
            } else {
                $text .= "\n{$key}:\n" . self::traitMessage($value) . "\n";
            }
        }

        return $text;
    }

}
