<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\CampaignsController;
use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\User;
use App\TinkoffService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DeelsPaymentCompatibilityController extends Controller
{
    public function donate(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:50'],
            'anonymous' => ['sometimes', 'boolean'],
        ]);

        $campaign = Campaign::query()
            ->where(function ($query) use ($id): void {
                $query->where('slug', $id);
                if (ctype_digit($id)) {
                    $query->orWhere('id', (int) $id);
                }
            })
            ->where('status', 1)
            ->firstOrFail();

        if ($campaign->is_ended()) {
            return response()->json([
                'success' => false,
                'message' => 'Копилка уже завершена',
            ], 422);
        }

        $user = $request->user();
        $amount = round((float) $validated['amount'], 2);
        $anonymous = (bool) ($validated['anonymous'] ?? false);
        $orderId = time().'_'.$user->id.'_'.$campaign->id.($anonymous ? '_anon' : '');

        $terminalKey = (string) config('services.tinkoff.terminal_key');
        $secretKey = (string) config('services.tinkoff.secret_key');

        if ($terminalKey === '' || $secretKey === '') {
            Log::error('Tinkoff donation init failed: credentials are not configured');

            return response()->json([
                'success' => false,
                'message' => 'Платёжный сервис временно недоступен',
            ], 503);
        }

        $api = new TinkoffService($terminalKey, $secretKey);
        $amountKopecks = (int) round($amount * 100);
        $email = $user->email ?: 'anon@email.ru';

        $params = [
            'TerminalKey' => $terminalKey,
            'OrderId' => $orderId,
            'Amount' => $amountKopecks,
            'Taxation' => 'usn_income',
            'NotificationURL' => route('deels.compat.campaigns.donations.callback'),
            'SuccessURL' => route('deels.public.campaigns.show', ['slug' => $campaign->slug]),
            'FailURL' => route('deels.public.campaigns.show', ['slug' => $campaign->slug]),
            'Receipt' => [
                'Taxation' => 'usn_income',
                'Email' => $email,
                'Items' => [
                    [
                        'Name' => 'Донат в копилку '.htmlspecialchars(mb_substr((string) $campaign->title, 0, 75)),
                        'Price' => $amountKopecks,
                        'Quantity' => 1,
                        'Amount' => $amountKopecks,
                        'PaymentMethod' => 'full_payment',
                        'PaymentObject' => 'commodity',
                        'Tax' => 'none',
                    ],
                ],
            ],
            'DATA' => [
                'Email' => $email,
                'Connection_type' => 'deels-web',
            ],
        ];

        try {
            $raw = $api->init($params);
            $init = json_decode((string) $raw, true);
        } catch (\Throwable $e) {
            Log::error('Tinkoff donation init exception', [
                'campaign_id' => $campaign->id,
                'user_id' => $user->id,
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Не удалось создать платёж',
            ], 502);
        }

        $paymentUrl = is_array($init) ? ($init['PaymentURL'] ?? null) : null;
        if (!is_array($init) || empty($init['Success']) || !$paymentUrl) {
            Log::warning('Tinkoff donation init rejected', [
                'campaign_id' => $campaign->id,
                'user_id' => $user->id,
                'error_code' => $init['ErrorCode'] ?? null,
                'details' => $init['Details'] ?? null,
            ]);

            return response()->json([
                'success' => false,
                'message' => $init['Message'] ?? $init['Details'] ?? 'Платёжный сервис отклонил запрос',
            ], 502);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'payment_url' => $paymentUrl,
                'redirect_url' => $paymentUrl,
                'payment_id' => $init['PaymentId'] ?? null,
                'order_id' => $orderId,
            ],
        ]);
    }

    public function callback(Request $request)
    {
        $payload = json_decode($request->getContent(), true);
        if (!is_array($payload)) {
            return response('INVALID PAYLOAD', 400);
        }

        if (!$this->validNotificationToken($payload)) {
            Log::warning('Rejected invalid Tinkoff donation callback', [
                'payment_id' => $payload['PaymentId'] ?? null,
                'order_id' => $payload['OrderId'] ?? null,
            ]);

            return response('INVALID TOKEN', 403);
        }

        $order = explode('_', (string) ($payload['OrderId'] ?? ''));
        if (count($order) < 3 || !ctype_digit((string) $order[1]) || !ctype_digit((string) $order[2])) {
            Log::warning('Rejected malformed Tinkoff donation OrderId', [
                'order_id' => $payload['OrderId'] ?? null,
            ]);

            return response('INVALID ORDER', 400);
        }

        $userId = (int) $order[1];
        $anonymous = ($order[3] ?? null) === 'anon';
        $displayName = 'Анонимно';

        if (!$anonymous) {
            $user = User::find($userId);
            $displayName = $user?->username ?: $user?->name ?: 'Пользователь Deels';
        }

        $request->session()->put('cart.contributor_name_display', $displayName);

        return app(CampaignsController::class)->confirmPayment($request);
    }

    private function validNotificationToken(array $payload): bool
    {
        $received = (string) ($payload['Token'] ?? '');
        if ($received === '') {
            return false;
        }

        unset($payload['Token']);
        $payload['Password'] = (string) config('services.tinkoff.secret_key');
        ksort($payload);

        $source = '';
        foreach ($payload as $value) {
            if (is_array($value) || is_object($value)) {
                continue;
            }

            if (is_bool($value)) {
                $source .= $value ? 'true' : 'false';
            } elseif ($value !== null) {
                $source .= (string) $value;
            }
        }

        return hash_equals(hash('sha256', $source), $received);
    }
}
