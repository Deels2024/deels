<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\TinkoffService;
use Illuminate\Console\Command;

class GetPaymentsDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:get-payment';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $api = new TinkoffService(
            '1619081031059DEMO',
            'p1nztc3a1b74cgdd'
        );
    }
}
