<?php

declare(strict_types=1);

namespace App\Console\Commands\System;

use App\Jobs\Mail\ParseIncomeMails;
use Illuminate\Console\Command;

class ParseMailBoxes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'newsletters:income:filters';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Filter income mail boxes via imap';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $mailboxes = ['default', 'code'];

        foreach ($mailboxes as $mailbox) {
            try {
                $client = \Webklex\IMAP\Facades\Client::account($mailbox);
                $client->connect();
                $folder = $client->getFolder('INBOX');
                $per_page = 50;
                $messages_count = $folder->messages()->all()->count();
                $pages = intval(ceil($messages_count/$per_page));
                echo "\nStarting mailbox $mailbox parsing\n";
                $bar = $this->output->createProgressBar($pages);
                $bar->start();
                for ($i = 1; $i <= $pages; $i++) {
                    ParseIncomeMails::dispatch($mailbox, $i);
                    $bar->advance();
                }
                $bar->finish();
                echo "\nParsing mailbox $mailbox finished\n";
            } catch (\Throwable $e) {

            }

        }

    }
}
