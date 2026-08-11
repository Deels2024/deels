<?php

namespace App\Console\Commands;

use App\Http\Controllers\RatchetController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Factory;
use App\Http\Controllers\Api\WebSocketController;
use React\EventLoop\Factory as LoopFactory;
use React\Socket\Server as Reactor;

class WebSocketServer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'websocket:init';

    protected $loop;

    protected $lastRestart;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start socket server';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->loop = LoopFactory::create();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $this
            ->configureRestartTimer()
            ->startWebSocketServer();

    }

    public function configureRestartTimer()
    {
        $this->lastRestart = $this->getLastRestart();

        $this->loop->addPeriodicTimer(10, function () {
            if ($this->getLastRestart() != $this->lastRestart) {
                $this->loop->stop();
                echo "\e[31mChat server stopped \n";
                exit;
            }
        });

        return $this;
    }

    protected function getLastRestart()
    {
        return Cache::get('chat:server:restart', 0);
    }

    protected function startWebSocketServer()
    {
        try {
            $host = env('RATCHET_HOST') ? env('RATCHET_HOST') : 'ws://localhost';
            $port = env('RATCHET_PORT') ? env('RATCHET_PORT') : 8090;
            $server_port = 8090;
            echo "\e[33mChat server started on $host:$port\n";
            $loop = $this->loop;
            $socket = new Reactor('0.0.0.0:' . $server_port, $loop);
            $wsServer = new WsServer(new WebSocketController($loop));
            $server = new IoServer(new HttpServer($wsServer), $socket, $loop);
            $wsServer->enableKeepAlive($server->loop, 10);
            $server->run();
        } catch (\Throwable $e) {
            Log::info($e->getMessage());
            Log::info(json_encode($e));
        }
    }
}