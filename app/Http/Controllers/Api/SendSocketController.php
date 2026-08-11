<?php
namespace App\Http\Controllers\Api;

use App\Services\WSClient;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\WebSocket\WsConnection;
use App\Services\Mango\MangoHelper;

/**
 * @author Rohit Dhiman | @aimflaiims
 */
class SendSocketController extends BaseController
{

    public function testSocket(Request $request)
    {
        $message = $request->input('message');
        $to = $request->input('user_id');

        $host = env('RATCHET_HOST') ? env('RATCHET_HOST') : 'ws://localhost';
        $port = env('RATCHET_PORT') ? env('RATCHET_PORT') : 8090;
        \Ratchet\Client\connect($host.':'.$port)->then(function($conn) use($message, $to) {
            $conn->on('message', function($msg) use ($conn) {
                echo "Received: {$msg}\n";
                $conn->close();
            });

            $conn->send('{"command":"message","to":"'.$to.'","message":{"status":"'.$message.'"}}');
        }, function ($e) {
            echo "Could not connect: {$e->getMessage()}\n";
        });

        return response([
            'success' => true,
        ]);

    }
}