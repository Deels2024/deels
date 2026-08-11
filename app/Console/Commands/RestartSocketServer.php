<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\Cache;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\InteractsWithTime;

class RestartSocketServer extends Command
{
    use InteractsWithTime;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'websocket:restart';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restart socket server';

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
        Cache::forever('chat:server:restart', $this->currentTime());

        $this->info('Restarting chat server...');
    }
}
