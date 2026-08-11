<?php

declare(strict_types=1);

namespace App\Console\Commands\System;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ParseUsersEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'newsletters:users:emails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Filter users emails';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $users = User::where('email', 'like', '%.ua%')->whereNull('unsubscribe')->orWhere('email', 'like', '%ukr%')->whereNull('unsubscribe')->get();
        echo "\nStarting parsing emails\n";
        $bar = $this->output->createProgressBar(count($users));
        $bar->start();

        foreach ($users as $user) {
            $email = $user->email;
            $domain = explode('@', $email);
            $domain = explode('.', $domain[1]);
            if(Str::contains($user->email, 'ukr.net') || end($domain) == 'ua') {
               $user->unsubscribe = true;
               $user->save();
           }

        }
        $bar->finish();
        echo "\nParsing emails finished\n";

    }
}
