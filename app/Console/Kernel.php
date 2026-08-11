<?php

declare(strict_types=1);

namespace App\Console;

use App\Console\Commands\PayPendingReferrals;
use App\Console\Commands\SendMailings;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        if(env('DEV_SCHEDULE')) {

        } else {
            $schedule->command(SendMailings::class)
                ->everyMinute()
                ->withoutOverlapping();

            $schedule->command(PayPendingReferrals::class)
                ->everyMinute();

            $schedule->command('newsletters:mails:send')->everyMinute()->withoutOverlapping();

            $schedule->command('services:motivate')->dailyAt('12:00');
            $schedule->command('services:rebill')->dailyAt('12:00');
//            $schedule->command('campaigns:authors:activity-check')->everyMinute()->withoutOverlapping();

            $schedule->command('sitemap:generate')->dailyAt('8:00');
            $schedule->command('sitemap:generate')->dailyAt('16:00');

            $schedule->command('newsletters:users:emails')->dailyAt('8:00');

            // обновление базы mail-адресов в черном списке
            $schedule->command('erag:sync-disposable-email-list')
                ->weekly()
                ->withoutOverlapping();
            $schedule->command('newsletters:income:filters')->everyTwoHours();
            $schedule->command('queue:retry all')->everyThreeHours();
            $schedule->command('messages:robot:clear')->dailyAt('23:00');


            // уведомление перед платным хранением
            $schedule->command('stories:paid:notify')->hourly();

            // платное хранение сторис
            $schedule->command('stories:paid:storage')->hourly();

            $schedule->command('stories:blocked:storage')->hourly();

            $schedule->command('stories:tags:add')->twiceDaily(12, 23);

            // определение завершение челленджа
            $schedule->command('challenges:start')->everyMinute()->withoutOverlapping();

            // определение завершение челленджа
            $schedule->command('challenges:finish')->everyFiveMinutes()->withoutOverlapping();

            // автоопределение победителей, если автор не выбрал их за 3 дня
            $schedule->command('challenges:winners:resolve-pending')->hourly();

            // определение завершение батла
            $schedule->command('battles:start')->everyMinute()->withoutOverlapping();

            // определение завершение батла
            $schedule->command('battles:finish')->everyFiveMinutes()->withoutOverlapping();

            // системные тесты
            $schedule->command('chatgpt:test')->dailyAt('8:00');
            $schedule->command('6proxy:check')->dailyAt('23:00');
            $schedule->command('services:balances:collect')->everyThirtyMinutes()->withoutOverlapping();
//            $schedule->command('deels:stats')->dailyAt('23:00');
            $schedule->command('metrics:telegram daily')
                ->dailyAt('09:00')
                ->skip(fn () => now()->isMonday());
            $schedule->command('metrics:telegram weekly --retention --skip-unavailable-shards --retention-wait --retention-wait-attempts=30 --retention-wait-seconds=20')
                ->mondays()
                ->at('09:00');
            $schedule->command('metrics:google-sheet daily')
                ->dailyAt('09:05')
                ->skip(fn () => now()->isMonday());
            $schedule->command('metrics:google-sheet weekly --retention --skip-unavailable-shards --retention-wait --retention-wait-attempts=30 --retention-wait-seconds=20')
                ->mondays()
                ->at('09:05');

            // удаление неактивированных пользователей
//            $schedule->command('users:notactive:delete')->dailyAt('23:59');

            $schedule->command('users:pending-action:delete')
                ->dailyAt('23:50')
                ->withoutOverlapping();

            // запуск автолайков
//        $schedule->command('challenges:add:likes')->twiceDaily(23, 11);
//        $schedule->command('challenges:add:likes')->dailyAt('22:00');

            // размораживание челленджей
            $schedule->command('challenges:unfroze')->everyFiveMinutes();

            // размораживание батлов
            $schedule->command('battles:unfroze')->everyFiveMinutes();
        }



    }

    /**
     * Register the Closure based commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
