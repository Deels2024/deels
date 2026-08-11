<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Helpers\ChatGPTHelper;
use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Country;
use App\Models\Likes;
use App\Models\Media;
use App\Models\Payment;
use App\Models\Story;
use App\Models\Thanks;
use App\Models\User;
use App\Services\ChatGPT\ChatGPTContentService;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class ChatGPTController extends Controller
{

    use SendsPasswordResetEmails;

    public function ping(Request $request)
    {
        return response()->json(app(ChatGPTContentService::class)->ping());
    }

    public function moneybox(Request $request)
    {
        return response()->json(app(ChatGPTContentService::class)->moneybox($request));
    }

    public function copystories(Request $request)
    {
        return response()->json(app(ChatGPTContentService::class)->copystories($request));
    }

    public function thanks(Request $request)
    {
        $user_id = $request->input('user_id');
        $payment_id = $request->input('payment_id');
        $image = $request->input('image');
        $user = User::find($user_id) ?? Auth::user() ?? auth()->user() ?? null;
        $payment = Payment::find($payment_id);
        if(!$payment) {
            return response()->json([
                'success' => false,
                'error' => 'Донат не найден'
            ]);
        }
        if (!$user) {
            return response()->json([
                'success' => false,
                'error' => 'Пользователь не найден'
            ]);
        }

        $is_image = false;
        $ai_generate_cost = env('AI_THANKS_COST', 1000);
        if($payment->amount >= 100) {
            $is_image = true;
            $image = true;
            $ai_generate_cost = env('AI_THANKS_IMAGE_COST', 5000);
        }

        try {
            $user->wallet_withdraw($ai_generate_cost, ['donate' => 'ai', 'description' => 'Оплата за использование ИИ'], true);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => 'Недостаточно дилсов. Необходимо пополнить баланс на '.$ai_generate_cost.' '.trans_choice('numbers.coins', $ai_generate_cost ?? 0)
            ]);
        }

        $data = [];
        $campaign_user = $payment->campaign->user;
        $payment_user = $payment->user;
        $data['donation_recipient']['name'] = $campaign_user->name ?? $campaign_user->username;
        $data['donation_recipient']['gender'] = $campaign_user->gender ?? '';
        if($payment_user) {
            $data['donation_sender']['amount'] = $payment->amount*100;
            $data['donation_sender']['name'] = $payment->name ?? $payment->username;
            $data['donation_sender']['gender'] = $payment->gender ?? '';
        } else {
            $data['donation_sender']['amount'] = $payment->amount*100;
            $data['donation_sender']['name'] = '';
            $data['donation_sender']['gender'] = '';
        }
        $chatgpt = new ChatGPTHelper();
        if($image) {
            $chatgpt_response = $chatgpt->thanks_image($data);
        } else {
            $chatgpt_response = $chatgpt->thanks_text($data);
        }

        try {
            $data = [
                'receiver' => $payment->email,
                'payment_id' => $payment->id,
                'approved' => true,
                'moderated' => true,
            ];

            if($image) {
                $response_content = $chatgpt_response['data'][0]['b64_json'] ?? null;
                $folder = "uploads/thanks/".date('Y/m/d');
                $image = \Image::make($response_content)->encode('jpg', 90);
                $filename = md5(strval(Carbon::now()->timestamp)).'.jpg';
                \Storage::disk('public')->put($folder.'/'.$filename, $image->stream());
                $response_content = '/'.$folder.'/'.$filename;
                $data['data'] = [
                    'type' => 'image',
                    'payload' => $response_content
                ];
            } else {
                $response_content = $chatgpt_response['content'] ?? null;
                $data['data'] = [
                    'type' => 'comment',
                    'payload' => $response_content
                ];
            }
            try {
                $user->wallet_withdraw($ai_generate_cost, ['donate' => 'ai', 'description' => 'Оплата за использование ИИ']);
                Thanks::create($data);
            } catch (\Throwable $e) {
                return response()->json([
                    'success' => false,
                    'error' => 'Недостаточно дилсов. Необходимо пополнить баланс на '.$ai_generate_cost.' '.trans_choice('numbers.coins', $ai_generate_cost ?? 0)
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Благодарность успешно добавлена! Страница будет перезагружена.',
                'is_image' => $image ?? false,
                'content' => $response_content,
            ]);
        } catch (\Throwable $e) {
            Log::info($e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Сервис временно недоступен. Если ошибка будет повторяться - свяжитесь с администрацией.',
                'line' => $e->getLine(),
            ]);
        }
    }

    public function assistant_text(Request $request) {
        $text = $request->input('text');
        $chatgpt = new ChatGPTHelper();
        try {
            $chatgpt_response = $chatgpt->assistant_text($text);
            return $chatgpt_response;
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'data' => $e->getMessage(),
                'error' => 'Сервис временно недоступен. Если ошибка будет повторяться - свяжитесь с администрацией.',
            ]);
        }

    }

    public function moderation_text(Request $request) {
        $text = $request->input('text');
        $chatgpt = new ChatGPTHelper();
        try {
            $chatgpt_response = $chatgpt->moderation_text($text);
            return response()->json([
                'success' => true,
                'flagged' => $chatgpt_response['flagged'],
                'reason' => $chatgpt_response['reason'] ?? '',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'data' => $e->getMessage(),
                'error' => 'Сервис временно недоступен. Если ошибка будет повторяться - свяжитесь с администрацией.',
            ]);
        }

    }


    public function moderation_image(Request $request) {
        $image = $request->input('image');
        $chatgpt = new ChatGPTHelper();
        try {
            $chatgpt_response = $chatgpt->moderation_image($image);
            return response()->json([
                'success' => true,
                'flagged' => $chatgpt_response['flagged'],
                'reason' => $chatgpt_response['reason'] ?? '',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'data' => $e->getMessage(),
                'error' => 'Сервис временно недоступен. Если ошибка будет повторяться - свяжитесь с администрацией.',
            ]);
        }
    }

    public function moderation_video(Request $request) {
       $media = Media::find($request->input('media_id'));
        $chatgpt = new ChatGPTHelper();
        try {
            $chatgpt_response = $chatgpt->moderation_video($media);
            return response()->json([
                'success' => true,
                'flagged' => $chatgpt_response['flagged'],
                'reason' => $chatgpt_response['reason'] ?? '',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'data' => $e->getMessage(),
                'error' => 'Сервис временно недоступен. Если ошибка будет повторяться - свяжитесь с администрацией.',
            ]);
        }
    }
}
