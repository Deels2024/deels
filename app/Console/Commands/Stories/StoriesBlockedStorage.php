<?php

declare(strict_types=1);

namespace App\Console\Commands\Stories;

use App\Helpers\AppHelper;
use App\Helpers\ChatGPTHelper;
use App\Jobs\SendMotivateStoryMessage;
use App\Models\Story;
use App\Models\User;
use App\Notifications\UserEmail;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StoriesBlockedStorage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stories:blocked:storage';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get blocked stories need to pay for storage';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $user = User::find(78495);
        $helper = new AppHelper();
        $helper->chat_notify($user,'test test',null);

        $stories = Story::whereNotNull('blocked_at')->whereNull('challenge_id')->orderBy('created_at', 'DESC')->get();
//        echo "found ".count($stories)."\n";
        $helper = new AppHelper();
        foreach ($stories as $story) {
            try {
                $story->user->wallet_withdraw(intval(env('STORIES_STORAGE_COST', 50)), ['donate' => 'story', 'description' => 'Оплата за хранение сторис #'.$story->id]);
                $story->next_pay_at = Carbon::now()->addMonth();
                $story->blocked_at = null;
                $story->saveQuietly();
                $text = 'Списание '.intval(env('STORIES_STORAGE_COST', 50)).' дилсов за хранение сторис #'.$story->id.'. Следующая оплата запланирована на '.Carbon::now()->addMonth()->format('d.m.Y в H:i');
                $helper->chat_notify($story->user,$text,null);
                $story->user->notify(new UserEmail('Списание за хранение сторис #'.$story->id, $text));
//                echo "successfully paid"."\n";
            } catch (\Throwable $e) {
                if($story->blocked_at <= Carbon::now()) {
                    if(!$story->user->is_admin()) {
                        $story->delete();
                        $text = 'Сторис #'.$story->id.' была заблокирована за неуплату и удалена.';
                        $helper->chat_notify($story->user,$text);
                        $story->user->notify(new UserEmail('Сторис #'.$story->id.' удалена', $text));
                    }
//                    echo "failed paid. story deleted"."\n";
                }
            }
        }

    }
}
