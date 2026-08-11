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

class StoriesPaidStorage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stories:paid:storage';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get stories need to pay for storage';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $stories = Story::where(function ($query) {
            $query->where('created_at', '<=', Carbon::now()->subMonths(3))
                ->where('next_pay_at', '<=', Carbon::now())
                ->where('active', 1)
                ->where('declined', 0)
                ->whereNull('challenge_id')
                ->whereNull('blocked_at');
        })->orWhere(function ($query) {
            $query->where('created_at', '<=', Carbon::now()->subMonths(3))
                ->where('next_pay_at', null)
                ->where('active', 1)
                ->where('declined', 0)
                ->whereNull('challenge_id')
                ->whereNull('blocked_at');
        })->orderBy('created_at', 'DESC')->get();

        $paid = 0;
        $blocked = 0;
        echo "Stories < ".Carbon::now()->subMonths(3)->format('d.m.Y H:i')."\n";
        echo "Count: ".count($stories)."\n";
        $helper = new AppHelper();
        foreach ($stories as $story) {
            try {
                if($story->challenge_id && $story->challenge && !$story->challenge->finished) {
                    continue;
                } else {
                    if(!$story->user->is_admin()) {
                        $story->user->wallet_withdraw(intval(env('STORIES_STORAGE_COST', 50)), ['donate' => 'story', 'description' => 'Оплата за хранение сторис #'.$story->id]);
                        $story->next_pay_at = Carbon::now()->addMonth();
                        $story->blocked_at = null;
                        $story->storage_notify = false;
                        $story->saveQuietly();
                        $paid++;
                        $text = 'Списание '.intval(env('STORIES_STORAGE_COST', 50)).' дилсов за хранение сторис #'.$story->id.'. Следующая оплата запланирована на '.Carbon::now()->addMonth()->format('d.m.Y в H:i');
                        $helper->chat_notify($story->user,$text,null);
                        $story->user->notify(new UserEmail('Списание за хранение сторис #'.$story->id, $text));
                    }
                }

            } catch (\Throwable $e) {

                $button = [
                    'type' => 'action',
                    'action' => 'deposit',
                    'text' => 'Пополнить баланс',
                    'url' => route('user_wallet').'?deposit=true'
                ];
                $blocked_at = Carbon::now()->addWeeks(2);
                $story->blocked_at = $blocked_at;
                $story->storage_notify = false;
                $story->saveQuietly();
                $text = 'Сторис #'.$story->id.' заблокирована и будет удалена '.$blocked_at->format('d.m.Y в H:i').'. Необходимо пополнить баланс на '.env('STORIES_STORAGE_COST', 50).' дилсов для ежемесячной оплаты за хранение сторис.';
                $helper->chat_notify($story->user,$text,$button);
                $story->user->notify(new UserEmail('Сторис #'.$story->id.' заблокирована и будет удалена', $text));
                $blocked++;
            }
        }

        echo "Paid: $paid\n";
        echo "Blocked: $blocked\n";

        if($paid > 0 || $blocked > 0) {
            $telegram = new AppHelper();
            $telegram->telegram_message('Paid: '.$paid.' | Blocked: '.$blocked);
        }

    }
}
