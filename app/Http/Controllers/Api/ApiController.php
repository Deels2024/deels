<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Jobs\FireBaseEvent;
use App\Models\Campaign;
use App\Models\Country;
use App\Models\User;
use App\Models\UserEvent;
use App\Services\ApiAccountInfoService;
use App\Services\ApiStoriesFeedService;
use App\Services\ApiStoryFeedFormatter;
use App\Services\ApiTokenAuthService;
use App\Services\ReferralBonusService;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Vyuldashev\LaravelOpenApi\Attributes as OpenApi;

#[OpenApi\PathItem]
class ApiController extends Controller
{
    use SendsPasswordResetEmails;

    /**
     * Create new user.
     *
     * Creates new user or returns already existing user by email.
     */
    #[OpenApi\Operation(tags: ['Auth'])]
    public function create_token(Request $request)
    {
        $request->validate([
            'email' => 'required|string',
            'password' => 'required',
            'device_name' => 'required',
        ]);

        $result = app(ApiTokenAuthService::class)->createToken($request);

        return response()->json($result['payload'], $result['status']);
    }

    public function account_info($id = null, $only_data = false, $just_user_info = false)
    {
        $userData = app(ApiAccountInfoService::class)->build($id, (bool) $just_user_info);

        if (! $userData) {
            return response()->json([
                'success' => false,
                'error' => 'User ID '.$id.' not found',
            ]);
        }

        if ($only_data) {
            return $userData;
        }

        return response()->json([
            'success' => true,
            'data' => $userData,
        ]);
    }

    public function dismissEvent($event)
    {
        $event = UserEvent::findOrFail((int) $event);
        abort_unless((int) $event->user_id === (int) auth()->id(), 404);

        if (! $event->dismissed_at) {
            $event->forceFill(['dismissed_at' => now()])->save();
        }

        return response()->json(['success' => true]);
    }

    public function get_stories(Request $request)
    {
        $feed = app(ApiStoriesFeedService::class)->build($request);
        $media = $feed->media;
        $user_id = $feed->userId;
        $excludeIds = $feed->excludeIds;
        $requestedPage = $feed->requestedPage;

        if (request()->wantsJson()) {
            $formatter = app(ApiStoryFeedFormatter::class);

            $html = view('stories.partials.list_items', ['stories' => $media])->render();
            $data = $formatter->format($media, $user_id);

            return response()->json(array_merge([
                'success' => true,
                'data' => $data,
                'html' => $html,
            ], $this->feedPaginationMeta($media, $excludeIds, $requestedPage)));
        }

        if ($request->ajax()) {
            return response()->json(array_merge([
                'success' => true,
                'html' => view('stories.partials.list_items', ['stories' => $media])->render(),
            ], $this->feedPaginationMeta($media, $excludeIds, $requestedPage)));
        }

        return view('stories_index', ['stories' => $media]);

    }

    public function getCampaignPaymentUrl(Request $request)
    {
        $rules['campaign_id'] = 'required';
        $rules['user_id'] = 'required';
        $rules['amount'] = 'required';
        $validator = validator($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ]);
        }
        $user = User::find($request->user_id);
        if (! $user) {
            return response()->json([
                'success' => false,
                'errors' => 'Пользователь не найден',
            ]);
        }
        $campaign = Campaign::find($request->campaign_id);
        if (! $campaign) {
            return response()->json([
                'success' => false,
                'errors' => 'Копилка не найдена',
            ]);
        }
        $orderID = time().'_'.(auth()->id() ?? 'anon').'_'.$request->campaign_id;

        $terminalKey = config('services.tinkoff.terminal_key');
        $api = new \App\TinkoffService(
            $terminalKey,
            config('services.tinkoff.secret_key')
        );

        $donation_amount = $request->amount;

        if ($donation_amount < 50) {
            return response()->json([
                'success' => false,
                'errors' => 'amount должен быть больше или равен 50',
            ]);
        }

        $params = [
            'TerminalKey' => $terminalKey,
            'OrderId' => $orderID,
            'Amount' => $donation_amount * 100,
            'Taxation' => 'usn_income',
            'SuccessURL' => url('/campaign/'.$campaign->slug),
            'FailURL' => url('/campaign/'.$campaign->slug),
            'Receipt' => [
                'Taxation' => 'usn_income',
                'Email' => auth()->user()?->email ?? $user->email ?? 'anon@email.ru',
                'Items' => [
                    [
                        'Name' => 'Донат в копилку '.htmlspecialchars(mb_substr($campaign->title, 0, 75)),
                        'Price' => $donation_amount * 100,
                        'Quantity' => 1,
                        'Amount' => $donation_amount * 100,
                        'PaymentMethod' => 'full_payment',
                        'PaymentObject' => 'commodity',
                        'Tax' => 'none',
                    ],
                ],
            ],
            'DATA' => [
                'Email' => auth()->user()?->email ?? $user->email ?? 'anon@email.ru',
                'Connection_type' => 'example',
            ],
        ];

        $init = json_decode($api->init($params), true);

        return response()->json([
            'success' => true,
            'errors' => $init,
        ]);

    }

    private function feedPaginationMeta($media, array $excludeIds, int $requestedPage): array
    {
        return [
            'current_page' => ! empty($excludeIds) ? $requestedPage : $media->currentPage(),
            'total_pages' => $media->lastPage(),
            'has_more' => $media->hasMorePages(),
        ];
    }

    public function countries_list()
    {
        $countries = Country::all();
        $data = [];
        foreach ($countries as $country) {
            $data[] = [
                'category_id' => $country->id,
                'title' => $country->name_ru,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function sendResetLinkEmail(Request $request)
    {
        $validator = validator($request->all(), ['email' => 'required|email']);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ]);
        }

        $response = $this->broker()->sendResetLink(
            $this->credentials($request)
        );

        $response == Password::RESET_LINK_SENT
            ? $this->sendResetLinkResponse($request, $response)
            : $this->sendResetLinkFailedResponse($request, $response);

        return response()->json([
            'success' => true,
            'data' => $response,
        ]);
    }

    public function start_campaign(Request $request)
    {
        $user_id = $request->input('user_id') ?? request()->user()?->id;
    }

    public function swagger()
    {
        return view('admin.swagger');
    }

    public function stream_donate(Request $request, $id = null)
    {
        $user_id = $request->input('user_id') ?? request()->user()?->id;
        $amount = $request->input('amount');
        if ($amount <= 0) {
            return response()->json([
                'success' => false,
                'error' => 'Укажите сумму',
            ]);
        }
        $user = null;
        if ($user_id) {
            $user = User::find($user_id);
        }

        if (! $user && ! request()->user()) {
            return response()->json([
                'success' => false,
                'error' => 'Требуется авторизация',
            ]);
        } else {
            try {
                $user->wallet_withdraw(intval($amount), ['donate' => 'stream', 'description' => 'Оплата за стрим']);
                app(ReferralBonusService::class)->awardForFirstDonate($user);

            } catch (\Throwable $e) {
                return response()->json([
                    'success' => false,
                    'amount' => $amount,
                    'balance' => intval($user->balance),
                    'error' => $e->getMessage(),
                ]);
            }

        }

        return response()->json([
            'success' => true,
        ]);

    }

    public function stream_status()
    {
        $helper = new AppHelper;

        return $helper->twitch_status();
    }

    public function coins_bank()
    {
        $deels_bank_user = \App\Models\User::where('email', 'moderdeels@mail.ru')->first();
        $transactions_total = \Bavix\Wallet\Models\Transaction::where('meta', 'like', '%"get":"coins","old_connected"%')->sum('amount');
        if ($deels_bank_user) {
            $deels_wallet_balance = intval($deels_bank_user->wallet_balance ?? 0);
            $bank = intval($deels_wallet_balance - intval($transactions_total));
            //                                    $bank = $deels_wallet_balance;
            if ($bank < 0) {
                $bank = 0;
            }
        } else {
            $bank = intval(10000000 - $transactions_total);
        }
        $bank = str_pad(strval($bank), 8, '0', STR_PAD_LEFT);

        return response()->json([
            'success' => true,
            'count' => $bank,
        ]);
    }

    public function sendPush(Request $request)
    {
        $user_id = $request->input('user_id');
        $message = $request->input('message');
        $push = $request->input('push');
        if ($push) {
            FireBaseEvent::dispatch($user_id, $message);
        }

        return response()->json([
            'success' => true,
        ]);
    }

    public function test_upload(Request $request)
    {
        $request->validate([
            'file' => 'required',
        ]);

        return response()->json([
            'success' => true,
        ]);
    }
}
