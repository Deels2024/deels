<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;

class WebNotificationController extends Controller
{

    public function __construct()
    {

    }

    public function index()
    {
        return view('home');
    }

    public function storeToken(Request $request)
    {
        $user = auth()->user();
        if ($request->input('user_id')) {
            $user = User::find($request->input('user_id'));
        }
        if ($user) {
            $user->update(['device_key' => $request->token]);
            return response()->json([
                'success' => true,
                'message' => 'Token successfully stored for User ID ' . $user->id
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ]);
        }

    }

    public function sendWebNotification(Request $request)
    {
        $user_id = $request->input('user_id');
        $title = $request->input('title');
        $body = $request->input('body');
        $FcmToken = User::where('id', $user_id)->whereNotNull('device_key')->pluck('device_key')->first();
        if ($FcmToken) {
            $firebase = (new Factory)
                ->withServiceAccount(storage_path('app/deels-d1e43-firebase-adminsdk-6pgvy-ef1587318c.json'));

            $messaging = $firebase->createMessaging();

            $message = CloudMessage::fromArray([
                'notification' => [
                    'title' => $title,
                    'body' => $body
                ],
                'token' => $FcmToken,

            ]);

            $messaging->send($message);
        }

        return response()->json(['message' => 'Push notification sent successfully']);
    }
}