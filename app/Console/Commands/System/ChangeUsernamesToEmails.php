<?php

declare(strict_types=1);

namespace App\Console\Commands\System;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ChangeUsernamesToEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'usernames:change:emails';

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

        $users = User::where('username', 'like', '%username_%')->get();
        $count = 0;
        foreach ($users as $user) {
            $news_name = substr($user->email, 0, strrpos($user->email, '@'));
            $user->username = $news_name;
            $user->saveQuietly();
            $count++;
        }

        echo "Done $count\n";

    }
}
