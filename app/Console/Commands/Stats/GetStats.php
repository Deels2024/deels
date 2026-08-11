<?php

declare(strict_types=1);

namespace App\Console\Commands\Stats;

use App\Helpers\AppHelper;
use App\Models\Campaign;
use App\Models\Challenge;
use App\Models\Comment;
use App\Models\Likes;
use App\Models\Story;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GetStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deels:stats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Stats';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $telegram = new AppHelper();
        try {
            //stats

//            $telegram->telegram_message('GetStats service activated');

            $today = Carbon::now()->format('Y-m-d H:i:s');
            $last_week = Carbon::now()->subDays(7)->format('Y-m-d 00:00:00');
            $yesterday = Carbon::now()->format('Y-m-d 00:00:00');
            $campaigns = Campaign::count();
            $campaigns_yesterday = Campaign::where('created_at', '>=', $yesterday)->where('created_at', '<=', $today)->count();

            $users = User::count();
            $users_yesterday = User::where('created_at', '>=', $yesterday)->where('created_at', '<=', $today)->where('is_activated', true)->count();

            $stories = Story::where('declined', 0)->where('active', 1)->whereNull('challenge_id')->count();
            $stories_paid = Story::where('amount', '>', 0)->where('active', 1)->where('declined', 0)->whereNull('challenge_id')->count();
            $stories_yesterday = Story::where('created_at', '>=', $yesterday)->where('active', 1)->where('declined', 0)->whereNull('challenge_id')->where('created_at', '<=', $today)->count();
            $challenges_stories_yesterday = Story::where('created_at', '>=', $yesterday)->where('active', 1)->where('declined', 0)->whereNotNull('challenge_id')->where('created_at', '<=', $today)->count();

            $challenges = Challenge::count();
            $challenges_yesterday = Challenge::where('created_at', '>=', $yesterday)->where('created_at', '<=', $today)->count();

            $comments = Comment::count();
            $comments_yesterday = Comment::where('created_at', '>=', $yesterday)->where('created_at', '<=', $today)->count();

            $likes = Likes::count();
            $likes_yesterday = Likes::where('created_at', '>=', $yesterday)->where('created_at', '<=', $today)->count();


            $donate_stories = DB::table('transactions')->where('meta', 'like', '%"description":"Донат в сторис%')->where('amount', '<', 0)->where('created_at', '>=', $yesterday)->where('created_at', '<=', $today)->sum('amount');
            $donate_campaigns = DB::table('transactions')->where('meta', 'like', '%"donate":"campaign","description":"Оплата копилки%')->where('amount', '<', 0)->where('created_at', '>=', $yesterday)->where('created_at', '<=', $today)->sum('amount');
            $stories_storage = DB::table('transactions')->where('meta', 'like', '%"description":"Оплата за хранение сторис%')->where('amount', '<', 0)->where('created_at', '>=', $yesterday)->where('created_at', '<=', $today)->sum('amount');
            $ai_usage = DB::table('transactions')->where('meta', 'like', '%"description":"Оплата за использование ИИ%')->where('amount', '<', 0)->where('created_at', '>=', $yesterday)->where('created_at', '<=', $today)->sum('amount');

            $donate_stories_week = DB::table('transactions')->where('meta', 'like', '%"description":"Донат в сторис%')->where('amount', '<', 0)->where('created_at', '>=', $last_week)->where('created_at', '<=', $today)->sum('amount');
            $donate_campaigns_week = DB::table('transactions')->where('meta', 'like', '%"donate":"campaign","description":"Оплата копилки%')->where('amount', '<', 0)->where('created_at', '>=', $last_week)->where('created_at', '<=', $today)->sum('amount');
            $stories_storage_week = DB::table('transactions')->where('meta', 'like', '%"description":"Оплата за хранение сторис%')->where('amount', '<', 0)->where('created_at', '>=', $last_week)->where('created_at', '<=', $today)->sum('amount');
            $ai_usage_week = DB::table('transactions')->where('meta', 'like', '%"description":"Оплата за использование ИИ%')->where('amount', '<', 0)->where('created_at', '>=', $last_week)->where('created_at', '<=', $today)->sum('amount');

            $dictionary = [
                'campaigns' => 'Копилок всего',
                'campaigns_yesterday' => 'Новые копилки за сегодня',
                'users' => 'Пользователей всего',
                'users_yesterday' => 'Регистраций за сегодня',

                'stories' => 'Сторис всего',
                'stories_paid' => 'Сторис платных всего',
                'stories_yesterday' => 'Новые сторис за сегодня',

                'challenges' => 'Челенджей всего',
                'challenges_yesterday' => 'Новые челенджей за сегодня',
                'challenges_stories_yesterday' => 'Новые участники челленджей за сегодня',

                'comments' => 'Комментариев всего',
                'comments_yesterday' => 'Новых комментариев за сегодня',

                'likes' => 'Лайков всего',
                'likes_yesterday' => 'Новых лайков за сегодня',

                'donate_stories' => 'Донатов в сторис за сегодня',
                'donate_stories_week' => 'Донатов в сторис за неделю',
                'stories_storage' => 'Оплата за хранение сторис за сегодня',
                'stories_storage_week' => 'Оплата за хранение сторис за неделю',

                'donate_campaigns' => 'Донатов в копилки за сегодня',
                'donate_campaigns_week' => 'Донатов в копилки за неделю',

                'ai_usage' => 'Использование ИИ за сегодня',
                'ai_usage_week' => 'Использование ИИ за неделю',
            ];
            $data = [
                'campaigns' => $campaigns,
                'stories' => $stories,
                'stories_paid' => $stories_paid,
                'challenges' => $challenges,

                'devide0' => '---',

                'campaigns_yesterday' => $campaigns_yesterday,
                'stories_yesterday' => $stories_yesterday,
                'challenges_yesterday' => $challenges_yesterday,
                'challenges_stories_yesterday' => $challenges_stories_yesterday,

                'devide_гыукы' => '---',
                'users' => $users,
                'users_yesterday' => $users_yesterday,

                'devide1' => '--- ❣️ ---',
                'comments' => $comments,
                'comments_yesterday' => $comments_yesterday,
                'likes' => $likes,
                'likes_yesterday' => $likes_yesterday,
                'devide2' => '--- 💵 ---',
                'donate_stories' => abs(intval($donate_stories)),
                'stories_storage' => abs(intval($stories_storage)),
                'donate_campaigns' => abs(intval($donate_campaigns)),
                'ai_usage' => abs(intval($ai_usage)),
                'devide3' => '--- 💵 ---',
                'donate_stories_week' => abs(intval($donate_stories_week)),
                'stories_storage_week' => abs(intval($stories_storage_week)),
                'donate_campaigns_week' => abs(intval($donate_campaigns_week)),
                'ai_usage_week' => abs(intval($ai_usage_week)),
            ];
            $reply = "\n⭐️Статистика DEELS⭐️\n\n";
            $yesterday_title = Carbon::parse($yesterday)->format('H:i d.m.Y');
            $last_week_title = Carbon::parse($last_week)->format('H:i d.m.Y');
            $reply .= "\n🗓Неделя с $last_week_title\n\n";
            $reply .= "----\n";
            foreach ($data as $type => $value) {
                if(isset($dictionary[$type])) {
                    $reply .= $dictionary[$type].": ".$value."\n";
                } else {
                    $reply .= "\n".$value."\n\n";
                }
            }

            $reply .= "\n#статистика\n";

            $telegram->telegram_group_message($reply);
            //$telegram->telegram_group_message('');
        } catch (\Throwable $e) {
            $telegram->telegram_message('GetStats service error: '.$e->getMessage());
        }

    }
}
