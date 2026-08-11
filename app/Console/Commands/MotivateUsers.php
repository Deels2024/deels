<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Helpers\AppHelper;
use App\Helpers\ChatGPTHelper;
use App\Jobs\SendMotivateStoryMessage;
use App\Models\Story;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MotivateUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'services:motivate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get users with campaigns and no stories current day';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $telegram = new AppHelper();
        $telegram->telegram_message('MotivateUsers start');

        $campaigns_users = DB::table('campaigns')->where('status', 1)->distinct()->select('user_id')->pluck('user_id')->toArray();
        $chatgpt = new ChatGPTHelper();
        $count = 0;
        try {
            $motivation = $chatgpt->motivation('Юзернейм');
            $motivation_text = $motivation;

            foreach ($campaigns_users as $user_id) {
                $story = Story::where('user_id', $user_id)
                    ->where('created_at', '>=', Carbon::now()->format('Y-m-d 00:00:00'))
                    ->where('created_at', '<=', Carbon::now()->format('Y-m-d H:i:s'))
                    ->first();

                if (!$story) {
                    SendMotivateStoryMessage::dispatch($user_id, $motivation_text);
                    $count++;
                }
            }
        } catch (\Throwable $e) {
            Log::info('MotivateUsers ' . $e->getMessage());
            $telegram = new AppHelper();
            $telegram->telegram_message('MotivateUsers error:' . $e->getMessage());
        }

        echo "Done $count users\n";

        $telegram->telegram_message('Done '.$count.' users');

    }
}
