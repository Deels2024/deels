<?php

declare(strict_types=1);

namespace App\Console\Commands\Stories;

use App\Helpers\AppHelper;
use App\Helpers\ChatGPTHelper;
use App\Jobs\SendMotivateStoryMessage;
use App\Models\Story;
use App\Notifications\StoryNotification;
use App\Notifications\UserEmail;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StoriesPaidStorageNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stories:paid:notify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $helper = new AppHelper();
        $stories = Story::where(function ($query) {
            $query->where('created_at', '<=', Carbon::now()->subMonths(3)->subDays(3))
                ->where('next_pay_at', '<=', Carbon::now()->subDays(3))
                ->where('active', 1)
                ->where('declined', 0)
                ->whereNull('challenge_id')
                ->where('storage_notify', 0)
                ->whereNull('blocked_at');
        })->orWhere(function ($query) {
            $query->where('created_at', '<=', Carbon::now()->subMonths(3)->subDays(3))
                ->where('next_pay_at', null)
                ->where('active', 1)
                ->where('declined', 0)
                ->whereNull('challenge_id')
                ->where('storage_notify', 0)
                ->whereNull('blocked_at');
        })->orderBy('created_at', 'DESC')->get();


        echo "Stories < ".Carbon::now()->subMonths(3)->subDays(3)->format('d.m.Y H:i')."\n";
        echo "Count: ".count($stories)."\n";

        foreach ($stories as $story) {
            try {
                if($story->challenge_id && $story->challenge && !$story->challenge->finished) {
                    continue;
                } else {
                    if(!$story->user->is_admin()) {
                        $text = 'Через 3 дня будет списано '.intval(env('STORIES_STORAGE_COST', 50)).' дилсов за хранение сторис #'.$story->id.'. Хранение сторис является платной услугой, чтобы избежать удаления Вашей сторис, проверьте свой баланс в кошельке.';
                        $helper->chat_notify($story->user,$text,null);
                        $story->user->notify(new UserEmail('Запланировано списание за хранение сторис #'.$story->id, $text));
                        $story->storage_notify = true;
                        $story->saveQuietly();
                    }
                }

            } catch (\Throwable $e) {
            }
        }


    }
}
