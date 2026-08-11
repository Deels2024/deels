<?php

namespace App\Http\Controllers\Api;

use App\Jobs\User\UpdateUsersBalance;
use App\Models\User;
use App\Services\WSClient;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\WebSocket\WsConnection;

/**
 * @author Rohit Dhiman | @aimflaiims
 */
class WebSocketController implements MessageComponentInterface
{
    protected $clients;
    private $subscriptions;
    private $users;
    private $userresources;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
        $this->subscriptions = [];
        $this->users = [];
        $this->userresources = [];
    }

    /**
     * [onOpen description]
     * @method onOpen
     * @param ConnectionInterface $conn [description]
     * @return [JSON]                    [description]
     * @example connection               var conn = new WebSocket('ws://localhost:8090');
     */
    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);
        $this->users[$conn->resourceId] = $conn;
    }

    /**
     * [onMessage description]
     * @method onMessage
     * @param ConnectionInterface $conn [description]
     * @param  [JSON.stringify]              $msg  [description]
     * @return [JSON]                    [description]
     * @example subscribe                conn.send(JSON.stringify({command: "subscribe", channel: "global"}));
     * @example groupchat                conn.send(JSON.stringify({command: "groupchat", message: "hello glob", channel: "global"}));
     * @example message                  conn.send(JSON.stringify({command: "message", to: "1", from: "9", message: "it needs xss protection"}));
     * @example register                 conn.send(JSON.stringify({command: "register", userId: 9}));
     */

    /**
     * @return \SplObjectStorage
     */

    public function onMessage(ConnectionInterface $conn, $msg)
    {
        echo $msg;
        echo "\n";
        $data = json_decode($msg);
        if (isset($data->command)) {
            switch ($data->command) {
                case "message":
                    //
                    if (isset($this->userresources[$data->to])) {
                        foreach ($this->userresources[$data->to] as $key => $resourceId) {
                            if (isset($this->users[$resourceId])) {
                                $this->users[$resourceId]->send(json_encode($data->message));
                            }
                        }
                        $conn->send(json_encode($this->userresources[$data->to]));
                    }
                    break;
                case "register":
                    $this->userresources[$conn->resourceId]['user_id'] = $data->userId ?? null;
                    $this->set_connected($data->userId);
                    if (isset($data->userId)) {
                        if (isset($this->userresources[$data->userId])) {
                            if (!in_array($conn->resourceId, $this->userresources[$data->userId])) {
                                $this->userresources[$data->userId][] = $conn->resourceId;
                            }
                        } else {
                            $this->userresources[$data->userId] = [];
                            $this->userresources[$data->userId][] = $conn->resourceId;
                        }
                    }
                    $conn->send(json_encode(['status' => 'success']));
                    break;
                case "online":
                    $this->userresources[$conn->resourceId]['user_id'] = $data->user_id ?? null;
                    $conn->send(json_encode(['message' => 'qqq']));
                    $this->set_connected($data->user_id);
                    break;

                default:
                    $example = array(
                        'methods' => [
                            "subscribe" => '{command: "subscribe", channel: "global"}',
                            "groupchat" => '{command: "groupchat", message: "hello glob", channel: "global"}',
                            "message" => '{command: "message", to: "1", message: "it needs xss protection"}',
                            "register" => '{command: "register", userId: 9}',
                        ],
                    );
                    $conn->send(json_encode($example));
                    break;
            }
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);
        echo "Connection {$conn->resourceId} has disconnected\n";

        $user_id = $this->userresources[$conn->resourceId]['user_id'] ?? null;

        if ($user_id) {
            $this->set_disconnected($user_id);
        }


        unset($this->users[$conn->resourceId]);
        unset($this->subscriptions[$conn->resourceId]);
        unset($this->userresources[$conn->resourceId]);

        foreach ($this->userresources as &$userId) {
            foreach ($userId as $key => $resourceId) {
                if ($resourceId == $conn->resourceId) {
                    unset($userId[$key]);
                }
            }
        }
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }


    public function set_connected($user_id)
    {
        $user = User::find($user_id);
        $now = Carbon::now();
        if ($user) {
            $meta_data = $user->meta_data;
            $old_connected = $meta_data['online']['connected'] ?? null;
            $meta_data['online']['connected'] = $now;
            $minutes = $meta_data['online']['minutes'] ?? 0;
            if ($old_connected) {
                $connected = Carbon::parse($old_connected);
                $diff_minutes = $connected->diffInMinutes($now);
//                $new_minutes = $minutes + $diff_minutes;
//                if ($new_minutes >= 60) {
//                    $coins_pack = intval($new_minutes / 60);
//                    $user->deposit($coins_pack * 50, ['get' => 'coins', 'old_connected' => $meta_data['online']['connected'], 'now' => $now, 'old_minutes' => $minutes, 'diff_minutes' => $diff_minutes, 'new_minutes' => $new_minutes, 'coins_pack' => $coins_pack]);
//                    $new_minutes = ($new_minutes - ($coins_pack * 60));
//                }
                if ($diff_minutes == 0) {
                    $meta_data['online']['connected'] = $old_connected;
                }
//                $meta_data['online']['minutes'] = $new_minutes;
            }

            $user->meta_data = $meta_data;
            $user->save();
        }
    }

    public function set_disconnected($user_id)
    {
        $user = User::find($user_id);
        $now = Carbon::now();
        if ($user) {
            $meta_data = $user->meta_data;
            $minutes = $meta_data['online']['minutes'] ?? 0;
            $meta_data['online']['disconnected'] = $now;
            $connected = Carbon::parse($meta_data['online']['connected']);
            $diff_minutes = $connected->diffInMinutes($now);
            $new_minutes = $minutes + $diff_minutes;
            if ($new_minutes >= 60) {
                $coins_pack = intval($new_minutes / 60);

                $new_minutes_left = ($new_minutes - ($coins_pack * 60));

                $data = ['old_connected' => Carbon::parse($meta_data['online']['connected'])->format('d.m.Y H:i:s'), 'disconnected' => Carbon::parse($meta_data['online']['disconnected'])->format('d.m.Y H:i:s'), 'now' => $now,'old_minutes' => $minutes, 'diff_minutes' => $diff_minutes, 'new_minutes' => $new_minutes, 'coins_pack' => $coins_pack, 'left_minutes' => $new_minutes_left];
                UpdateUsersBalance::dispatch($user_id, $coins_pack, $meta_data, $data);

                $new_minutes = $new_minutes_left;
                if ($diff_minutes > 0) {
                    $meta_data['online']['connected'] = $now;
                }
            }
            $meta_data['online']['minutes'] = $new_minutes;
            $user->meta_data = $meta_data;
            $user->save();
        }
    }

}