<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Helpers\ChatGPTHelper;
use App\Jobs\Cdnvideo\TranscodeVideo;
use App\Jobs\Cdnvideo\UploadMediaToCdnvideo;
use App\Jobs\Cdnvideo\WarmCache;
use App\Jobs\SendMotivateStoryMessage;
use App\Jobs\Stories\ProcessVideo;
use App\Mail\ContactUs;
use App\Mail\ContactUsSendToSender;
use App\Models\Campaign;
use App\Models\Media;
use App\Models\Payment;
use App\Models\Story;
use App\Models\Thread;
use App\Models\User;
use App\Repositories\CampaignRepository;
use App\Services\Cdnvideo\CdnvideoClient;
use App\Services\RecommendationService;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use ReCaptcha\ReCaptcha;
use Juzaweb\HLSConverter\HLSConverter;
use Illuminate\Support\Facades\Http;
use Telegram\Bot\Api as TelegramApi;

class TestController extends Controller
{
    /**
     * Show the application dashboard.
     */
    public function index(Request $request)
    {

        abort(404);

        $story = Story::find(2120);
        $ads_data = $story->ads_data;
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
            ->post('https://ord-prestable.yandex.net/api/v6/creative', [
                'coBranding' => false,
                'contractIds' => ["1"],
                'creativeType' => "container",
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
                'urls' => $urls
            ]);

        // Проверка ответа
        if ($response->successful()) {
            $data = $response->json();
            dd($data);
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
            dd($message);
        }

        dd('s');
        $story = Story::find(2115);
        ProcessVideo::dispatchSync($story->id);
        dd('end');
        abort(404);
        $user = User::find(10);
        $recommendationService = new RecommendationService();
        $recommendations = $recommendationService->getRecommendationsForUser($user->id, 50);
        $rec_offset = 0;
        $per_page = 8;
        if($request->input('page') && $request->input('page') > 1) {
            $rec_offset = ($request->input('page')-1)*$per_page;
        }
        $recommendations = $recommendationService->getRecommendationsForUser($user->id, $per_page,$rec_offset);
        $storyIds = collect($recommendations)->pluck('story_id')->toArray();
        dd($storyIds);

        abort(404);
        $recommendationService = new RecommendationService();
        $recommendations = $recommendationService->getRecommendationsForUser(12, 50);
        dd($recommendations);
        dd('sadasdsa');
        abort(404);
        $media = Media::find(63502);
        WarmCache::dispatch($media);
        dd('ssss');
        $client = new CdnvideoClient();
        $accounts = $client->get('app/inventory/v1/accounts/');
        $account_name = $accounts[0]['name'];
                    $data2 = $client->get('app/storage/v1/' . $account_name . '/videos/683717110e47cf5bc9e1ca0d', [
                'account_name' => $account_name,
                'dir' => true,
            ]);

                    dd($data2);
        abort(404);
        $story = Story::find(1760);
        $media = Media::find(63502);
        $media = Media::find(63481);
        TranscodeVideo::dispatch($media);

//        UploadMediaToCdnvideo::dispatch($story->media);

        dd($story);
        abort(404);
        $username = $request->input('username');
        $moneybox = $request->input('moneybox');
        $chatgpt = new ChatGPTHelper();
        dd($chatgpt->motivation($username, $moneybox));
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

    public function chat_test(Request $request) {
        abort(404);
        $userIds = [12];
        $thread = Thread::whereJsonContains('users', $userIds)->get();
        dd($thread);
        if($request->input('user_id')) {
            SendMotivateStoryMessage::dispatch($request->input('user_id'));
        }

        $story = Story::find(13);
        $recipient = $story->user;
        $thread = $recipient->hasThread(0);
        dd($thread);
        $userIds = ["67124"];
        $thread = Thread::whereJsonContains('users', $userIds)->get();
        dd($thread);
    }
}
