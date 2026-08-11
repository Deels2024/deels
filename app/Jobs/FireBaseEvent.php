<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;

class FireBaseEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    private $user_id;
    private $message;
    private $model_id;
    private $action;

    public function __construct($user_id, $message, $model_id = null, $action = null)
    {
        $this->user_id = $user_id;
        $this->message = $message;
        $this->model_id = $model_id;
        $this->action = $action;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        try {
            $user_id = $this->user_id;
            $message = $this->message;
            $model_id = $this->model_id;
            $action = $this->action;

            $FcmToken = User::where('id', $user_id)->whereNotNull('device_key')->pluck('device_key')->first();

            if ($FcmToken) {
                $firebase = (new Factory)
                    ->withServiceAccount(storage_path('app/deels-d1e43-firebase-adminsdk-6pgvy-ef1587318c.json'));

                $messaging = $firebase->createMessaging();

                $message = CloudMessage::fromArray([
                    'notification' => [
//                    'title' => 'Новое событие!',
                        'body' => $message,
                        'data' => [
                            "action" => $action,
                            "model_id" => $model_id,
                        ]
                    ],
                    'token' => $FcmToken,
                ]);

                $messaging->send($message);
            }
        } catch (\Throwable $e) {

        }


    }
}
