<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Helpers\UserHelper;
use App\Http\Requests\UserUpdateRequest;
use App\Models\Campaign;
use App\Models\Comment;
use App\Models\Country;
use App\Models\Faq;
use App\Models\Likes;
use App\Models\Media;
use App\Models\NewsletterMail;
use App\Models\Payment;
use App\Models\Post;
use App\Models\Reward;
use App\Models\Story;
use App\Models\Transaction;
use App\Models\Update;
use App\Models\User;
use App\Models\WithdrawalPreference;
use App\Models\WithdrawalRequest;
use App\Notifications\AccountDeleteNotification;
use App\Rules\DisposableEmail;
use App\Services\Contests\ProfileContestService;
use App\Services\ProjectWalletService;
use App\Services\RegistrationCustomFieldService;
use App\Services\ReferralBonusService;
use App\Services\UserService;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Mail;
use NextApps\VerificationCode\VerificationCode;
use function count;
use function is_array;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $title = trans('app.users');
        $usersBuilder = User::query();
        $type = $request->get('type');
        $deleted = $request->get('deleted');
        $unsubscribed = $request->get('unsubscribed');
        if ($q = $request->get('q')) {
            $usersBuilder->where(function (Builder $query) use ($q): void {
                $query->where('name', 'like', "%$q%")
                    ->orWhere('email', 'like', "%$q%")
                    ->orWhere('username', 'like', "%$q%")
                    ->orWhere('id', $q);
            });
        }
        if ($type) {
            $usersBuilder->orderBy('created_at', 'desc');
        }


        if ($deleted) {
            $usersBuilder->withTrashed()->whereNotNull('deleted_at');
        }
        if ($unsubscribed) {
            $usersBuilder->where('unsubscribe', true);
        }
        if ($request->boolean('suspicious_moderation')) {
            $usersBuilder->where('suspicious_moderation_pending', true);
        }

        if ($request->has('excel')) {
            $take = 20;
            $skip = 0;
            if($request->has('page')) {
                $skip = $take * $request->has('page');
            }
            $users = $usersBuilder->orderBy('created_at', 'desc')->take($take)->skip($skip)->get();
            return $this->collectionToExcel($users);
        }
        $users = $usersBuilder->orderBy('created_at', 'desc')->paginate(20);
        $users_count = $usersBuilder->count();

        return view('admin.users_new', compact('title', 'users', 'users_count'));
    }

    public function show($id = 0)
    {
        if ($id) {
            $title = trans('app.profile');
            $user = User::find($id);

            $is_user_id_view = true;

            return view('admin.profile', compact('title', 'user', 'is_user_id_view'));
        }
    }

    /**
     * @param        $id
     * @param null $status
     *
     * @return RedirectResponse
     */
    public function statusChange($id, $status = null)
    {
        if (config('app.is_demo')) {
            return redirect()->back()->with('error', 'This feature has been disable for demo');
        }

        $user = User::find($id);
        if ($user && $status) {
            if ('approve' == $status) {
                $user->active_status = 1;
                $user->save();
            } elseif ('block' == $status) {
                $user->active_status = 2;
                $user->save();
            }
        }

        return back()->with('success', trans('app.status_updated'));
    }

    public function sendChatNotify(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'text' => ['required', 'string', 'max:5000'],
        ]);

        $user = User::findOrFail($id);
        $helper = new AppHelper();
        $helper->chat_notify($user, $request->input('text'), null);

        return back()->with('success', 'Сообщение отправлено пользователю');
    }

    public function suspiciousModeration(Request $request, int $id): RedirectResponse
    {
        $request->validate(['action' => ['required', 'in:skip,block']]);
        $user = User::findOrFail($id);

        if ($request->input('action') === 'block') {
            $user->active_status = 2;
        } else {
            $user->is_suspicious = false;
        }

        $user->suspicious_moderation_pending = false;
        $user->suspicious_violations = 0;
        $user->suspicious_moderation_requested_at = null;
        $user->suspicious_blocked_until = null;
        $user->save();

        return back()->with('success', 'Решение по подозрительному аккаунту сохранено');
    }

    public function profile()
    {
        $title = trans('app.profile');
        $user = Auth::user();

        return view('admin.profile', compact('title', 'user'));
    }

    public function user_profile($id, ProfileContestService $profileContests)
    {
        $user = User::findOrFail($id);
        $campaigns = $user->my_campaigns()->active()->orderBy('created_at', 'DESC')->get();
        $stories = $user->stories()->active()->get();
        $contestProfile = $profileContests->forProfile($user, Auth::user());
        $contests = $contestProfile['contests'];
        $hiddenContestsCount = $contestProfile['hidden_count'];
        $transactions = Transaction::where('payable_id', $user->id)->where('meta', 'like', '%{"donate"%')->sum('amount');
        $stories_ids = Story::where('user_id', $user->id)->pluck('id')->toArray();
        $campaigns_ids = Campaign::where('user_id', $user->id)->pluck('id')->toArray();
        $likes_count = Likes::whereIn('campaign_id', $campaigns_ids)->orWhereIn('story_id', $stories_ids)->count();
        return view('profile.profile', compact(
            'user',
            'campaigns',
            'stories',
            'contests',
            'hiddenContestsCount',
            'transactions',
            'likes_count'
        ));
    }

    public function profileSettings()
    {
        $title = trans('app.profile');
        $user = Auth::user();

        $russia_array = ["Russian Federation"];
        $sng_array = [
            "Azerbaijan", "Armenia", "Belarus", "Kazakhstan", "Kyrgyzstan", "Moldova, Republic of", "Tajikistan", "Uzbekistan",
        ];

        $russia = Country::whereIn('name', $russia_array)->get();
        $sng = Country::whereIn('name', $sng_array)->get();
        $all_sng = $russia->merge($sng);
        $sng_array = array_merge($russia_array, $sng_array);
        $not_sng = Country::whereNotIn('name', $sng_array)->get();
        $countries = $all_sng->merge($not_sng);

        if (!$user->connect_token) {
            $user->connect_token = md5(rand(1, 10) . microtime());
            $user->save();
        }

        return view('admin.profile_settings', compact('title', 'user', 'countries'));
    }

    public function profileEdit($id = null)
    {
        $title = trans('app.profile_edit');
        $user = Auth::user();

        if ($id) {
            $user = User::find($id);
        }

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $user,
                'first_message_followings_only' => (bool) ($user->first_message_followings_only ?? false),
            ]);
        }

        $myDonaters = [];
        if($user->referral_code) {
            $myDonaters = User::query()
                ->where('invite_referral_code', $user->referral_code)
                ->pluck('id');
        }


//        $myDonatersPayments = User::query()
//                                  ->whereIn('users.id', $myDonaters)
//                                  ->selectRaw('users.*, (select sum(amount) from transactions where holder_id=users.id) as paymentsAmount')
//                                  ->when(\request('asdasd'), fn(Builder $builder) => $builder->dd())
//                                  ->get();

//        $myDonatersPayments = User::query()
//            ->whereIn('users.id', $myDonaters) // Filter users based on given IDs
//            ->join('transactions', 'transactions.payable_id', '=', 'users.id') // Join with transactions
//            ->where('transactions.meta->type', 'payments') // Filter by JSON column
//            ->selectRaw('users.*,
//        SUM(CASE WHEN transactions.meta->>"$.type" = "payments" THEN transactions.amount ELSE 0 END) as paymentsAmount,
//        SUM(CASE WHEN transactions.meta->>"$.referral_profit" IS NOT NULL THEN transactions.meta->>"$.referral_profit" ELSE 0 END) as totalProfit')
//            ->groupBy('users.id')
//            ->get();

        $myDonatersPayments = User::query()
            ->whereIn('users.id', $myDonaters) // Filter users based on given IDs
            ->leftJoin('transactions', 'transactions.payable_id', '=', 'users.id') // Use left join instead
            ->where(function ($query) {
                $query->where('transactions.meta->type', 'payments') // Adding where condition inside function
                ->orWhereNull('transactions.id'); // Ensuring users with no transactions are included
            })
            ->selectRaw('users.*, 
        SUM(CASE WHEN transactions.meta->>"$.type" = "payments" THEN transactions.amount ELSE 0 END) as paymentsAmount,
        SUM(CASE WHEN transactions.meta->>"$.referral_profit" IS NOT NULL THEN transactions.meta->>"$.referral_profit" ELSE 0 END) as totalProfit')
            ->groupBy('users.id')
            ->get();

        $donatersDependencies = [
            [],
            ['donatersCount' => 5, 'donatePercent' => 0.25, 'widthOffset' => 8],
            ['donatersCount' => 10, 'donatePercent' => 0.5, 'widthOffset' => 25],
            ['donatersCount' => 50, 'donatePercent' => 0.75, 'widthOffset' => 48],
            ['donatersCount' => 100, 'donatePercent' => 1, 'widthOffset' => 73],
            ['donatersCount' => 200, 'donatePercent' => 1.5, 'widthOffset' => 100],
        ];

        $currentPosition = 0;
        foreach ($donatersDependencies as $k => $level) {
            if ($level && !empty($myDonaters)) {
                if (1 === $k && $myDonaters->count() < $level['donatersCount']) {
                    break;
                }
                if ($myDonaters->count() === $level['donatersCount']) {
                    $currentPosition = $k;
                    break;
                }
                if (isset($donatersDependencies[$k + 1]) && $myDonaters->count() > $level['donatersCount'] && $myDonaters->count() < $donatersDependencies[$k + 1]['donatersCount']) {
                    $currentPosition = $k;
                    break;
                }
            }
        }
        if (!$currentPosition) {
            $donatersPercent = 0;
        } else {
            $donatersPercent = $donatersDependencies[$currentPosition]['donatePercent'];
        }

        $offset = !empty($myDonaters) ? $myDonaters->count() / $donatersDependencies[$currentPosition + 1]['donatersCount'] * $donatersDependencies[$currentPosition + 1]['widthOffset'] : 0;
        $donatersLevelFill = !empty($myDonaters) ? $myDonaters->count() / $donatersDependencies[$currentPosition + 1]['donatersCount'] * 100 : 0;
        //        $donatersCount     = $donatersDependencies[$currentPosition]['donatersCount'];

        $inviters = DB::select('
select u.id, count(*)
from users u
         join users u2 on u.referral_code = u2.invite_referral_code
group by u.id
order by count(*) desc');
        $ratingPosition = User::count();
        foreach ($inviters as $k => $inviter) {
            if ((int)$inviter->id === Auth::id()) {
                $ratingPosition = $k + 1;
                break;
            }
        }

        $total_profit = $myDonatersPayments->sum('paymentsAmount') ?? 0;
        $total_profit_achieve = ($total_profit * $donatersPercent / 100);
        $project_wallet_balance = intval(app(ProjectWalletService::class)->wallet()->balance ?? 0);

        return view('admin.profile_edit', compact(
            'title',
            'user',
            'myDonatersPayments',
            'myDonaters',
            'donatersPercent',
            'offset',
            'ratingPosition',
            'donatersLevelFill',
            'currentPosition',
            'total_profit_achieve',
            'project_wallet_balance',
        ));
    }

    public function delete_confirm($token)
    {
        $user = User::where('delete_token', $token)->first();
        if ($user) {
            if ($user->wallet_balance > 0) {
                return redirect(route('user_wallet'))->with(['error' => 'Вам необходимо вывести дилсы']);
            }

            try {
                \Illuminate\Support\Facades\Mail::send(
                    [],
                    [],
                    function (Message $message) use ($user): void {
                        $message
                            ->from(env('MAIL_FROM_ADDRESS', 'info@deels.ru'), 'DEELS')
                            ->to('admin@deels.ru')
                            ->subject('Удаление пользователя')
                            ->html('Пользователь ' . $user->email . ' удалил свой аккаунт');
                    }
                );
            } catch (\Throwable $e) {

            }
            $user->delete();


            return redirect(route('home'))->with(['success' => 'Пользователь удален']);
        }
        return redirect(route('home'))->with(['fail' => 'Пользователь не найден']);
    }

    public function delete(Request $request)
    {
        $user = Auth::user() ?? auth()->user();
        if (!$user) {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Пользователь не найден',
                ]);
            }
        }
        if ($request->input('account_delete') && isset($user)) {
            $delete_token = Str::uuid();
            $user->delete_token = $delete_token;
            $btn = '<a href="' . route('profile.delete_confirm', $delete_token) . '" target="_blank">' . route('profile.delete_confirm', $delete_token) . '</a>';
            $text = 'Вы запросили удаление аккаунта. Для продолжения процедуры одтвердите действие перейдя по ссылке: ' . $btn;
            $user->save();
            $user->notify(new AccountDeleteNotification($text));
        }
        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Инструкция по удалению аккаунта отправлена на ваш e-mail',
            ]);
        }
        return back()->with(['success' => 'Инструкция по удалению аккаунта отправлена на ваш e-mail']);
    }


    public function delete_api(Request $request)
    {
        $user = Auth::user() ?? auth()->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'error' => 'Пользователь не найден',
            ]);
        }
        $delete_token = Str::uuid();
        $user->delete_token = $delete_token;
        $btn = '<a href="' . route('profile.delete_confirm', $delete_token) . '" target="_blank">' . route('profile.delete_confirm', $delete_token) . '</a>';
        $text = 'Вы запросили удаление аккаунта. Для продолжения процедуры одтвердите действие перейдя по ссылке: ' . $btn;
        $user->save();
        $user->notify(new AccountDeleteNotification($text));
        return response()->json([
            'success' => true,
            'message' => 'Инструкция по удалению аккаунта отправлена на ваш e-mail',
        ]);
    }


    public function update(UserUpdateRequest $request)
    {
        $id = $request->id;

        if (config('app.is_demo')) {
            return redirect()->back()->with('error', 'This feature has been disable for demo');
        }


        $user = Auth::user();
        if ($id) {
            $user = User::find($id);
        }

        $validatedData = $request->validated();

        if ($request->hasFile('files')) {
            $validatedData['avatar'] = (new MediaController())->store($request)['images'][0]->id;
        }

        if ($request->input('status')) {
            $validatedData['status'] = $request->input('status');
        } else {
            $validatedData['status'] = null;
        }
        $validatedData['first_message_followings_only'] = $request->boolean('first_message_followings_only');

        $user->update($validatedData);

        return back();
    }

    public function api_update(Request $request)
    {

        $user = auth()->user();

        $rules = [
            'email' => 'sometimes|email:rfc,dns',
            'name' => 'sometimes|string|min:2',
//            'last_name' => 'sometimes|string|min:2',
            'gender' => 'in:male,female',
            'username' => 'sometimes|unique:users,username,' . $user->id . ',id,deleted_at,NULL',
            'phone' => 'sometimes',
            'address' => 'sometimes',
            'avatar' => 'sometimes',
            'country_id' => 'sometimes',
            'status' => 'sometimes',
            'first_message_followings_only' => 'sometimes|boolean',
        ];
        $validator = validator($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ]);
        }
        $validatedData = $validator->validated();
        if ($request->hasFile('avatar')) {

            $userService = new UserService();

            $file = $userService->changeAvatar($request->file('avatar'));
            $validatedData['avatar'] = $file;
        }
        if (array_key_exists('phone', $validatedData)) {
            $validatedData['phone_hash'] = deels_phone_hash($validatedData['phone']);
        }
        if ($request->has('first_message_followings_only')) {
            $validatedData['first_message_followings_only'] = $request->boolean('first_message_followings_only');
        }

        $user->update($validatedData);

        return response()->json([
            'success' => true,
            'data' => $user,
        ]);
    }

    public function withdrawalPreference()
    {
        $title = trans('app.withdrawal_preference');
        $user = Auth::user();

        $countries = Country::orderBy('name', 'asc')->get();

        return view('admin.withdrawal_preference', compact('title', 'user', 'countries'));
    }

    public function withdrawalPreferenceUpdate(Request $request)
    {
        $user_id = Auth::user()->id;
        $rules = [
            'default_withdrawal_account' => 'required',
        ];
        $this->validate($request, $rules);

        $data = [
            'default_withdrawal_account' => $request->default_withdrawal_account,
            'paypal_email' => $request->paypal_email,
            'bank_account_holders_name' => $request->bank_account_holders_name,
            'bank_account_number' => $request->bank_account_number,
            'swift_code' => $request->swift_code,
            'bank_name_full' => $request->bank_name_full,
            'bank_branch_name' => $request->bank_branch_name,
            'bank_branch_city' => $request->bank_branch_city,
            'bank_branch_address' => $request->bank_branch_address,
            'country_id' => $request->country_id,
            'user_id' => $user_id,
        ];

        $withdrawal_preference = WithdrawalPreference::whereUserId($user_id)->first();
        if ($withdrawal_preference) {
            $withdrawal_preference->update($data);
        } else {
            WithdrawalPreference::create($data);
        }

        return redirect()->back()->with('success', trans('app.changes_has_been_saved'));
    }

    public function changePassword()
    {
        $title = trans('app.change_password');

        return view('admin.change_password', compact('title'));
    }

    public function changePasswordPost(Request $request)
    {
        if (config('app.is_demo')) {
            return redirect()->back()->with('error', 'This feature has been disable for demo');
        }
        $rules = [
            'old_password' => 'required',
            'new_password' => 'required|confirmed',
            'new_password_confirmation' => 'required',
        ];
        $this->validate($request, $rules);

        $old_password = $request->old_password;
        $new_password = $request->new_password;
        // $new_password_confirmation = $request->new_password_confirmation;

        if (Auth::check()) {
            $logged_user = Auth::user();

            if (Hash::check($old_password, $logged_user->password)) {
                $logged_user->password = Hash::make($new_password);
                $logged_user->save();

                return redirect()->back()->with('success', trans('app.password_changed_msg'));
            }

            return redirect()->back()->with('error', trans('app.wrong_old_password'));
        }
    }

    public function userDeleteByAdmin($id)
    {
        $user = User::find($id);
        $title = trans('app.account_deletion');

        return view('admin.account_deletion', compact('title', 'user'));
    }

    public function userAuthByAdmin($id)
    {
        Auth::logout(); // for end current session
        Auth::loginUsingId($id);
        Cookie::queue('auth-user', $id, 300);
        return redirect('/')->with('success', 'Вы авторизовались другим пользователем!');
    }


    public function accountDeletion()
    {
        $user = Auth::user();
        $title = trans('app.account_deletion');

        return view('admin.account_deletion', compact('title', 'user'));
    }

    public function accountDeletionPost()
    {
        $user = Auth::user();
        $time_now = Carbon::now()->toDateTimeString();

        $msg = __('app.account_deletion_requested');

        if (1 === $user->request_deletion) {
            $user->request_deletion = 2;
            $user->cancel_deletion_time = $time_now;
            $msg = __('app.deletion_request_canceled');
        } else {
            $user->request_deletion = 1;
            $user->request_deletion_time = $time_now;
        }

        $user->save();

        return back()->with('success', $msg);
    }

    /** @return Factory|View */
    public function requestedAccountDeletion()
    {
        $title = trans('app.account_deletion');
        $users = User::whereRequestDeletion(1)->get();

        return view('admin.requested_account_deletion', compact('title', 'users'));
    }

    /**
     * @param $id
     *
     * @return RedirectResponse|Redirector
     *
     * Delete account with all of it's assets
     */
    public function requestedDeletionAction(Request $request, $id)
    {
        $user = User::find($id);

        if (!Auth::user()->is_admin() || !$user) {
            abort(404, __('app.unauthorised_access'));
        }

        if ('cancel_deletion_request' === $request->action_type) {
            $user->request_deletion = null;
            $user->request_deletion_time = null;
            $user->cancel_deletion_time = null;
            $user->save();

            return back();
        } elseif ('approve_deletion_request' === $request->action_type) {
            Campaign::whereUserId($id)->delete();
            Comment::whereUserId($id)->delete();
            Faq::whereUserId($id)->delete();

            // Delete all media from disks
            $media_ids = Media::whereUserId($id)->pluck('id')->all();
            if (is_array($media_ids) && count($media_ids)) {
                $media_controller = new MediaController();
                $media_controller->delete($request, $media_ids);
            }

            Media::whereUserId($id)->delete();
            Payment::whereUserId($id)->delete();
            Post::whereUserId($id)->delete();
            Reward::whereUserId($id)->delete();
            Update::whereUserId($id)->delete();
            WithdrawalPreference::whereUserId($id)->delete();
            WithdrawalRequest::whereUserId($id)->delete();
            $user->delete();

            if ('users' === $request->redirect_page) {
                return redirect(route('users'))->with('success', 'Пользователь удален!');
            }
        }

        return redirect(route('requested_account_deletion'))->with('success', 'Пользователь удален!');
    }

    /** @throws Exception */
    public function sendCode(Request $request)
    {
        $customfield = app(RegistrationCustomFieldService::class);

        if ($customfield->isTripped($request)) {
            $customfield->ban($request);
        }

        if ($customfield->isBanned($request)) {
            return response()->json([
                'success' => false,
                'error' => 'Доступ заблокирован.',
                'banned' => true,
            ], 403);
        }

        $validator = validator($request->all(), [
            'email' => ['bail', 'required', 'email', 'max:255', 'ends_with:.ru', new DisposableEmail(), 'unique:users,email,NULL,id,deleted_at,NULL'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors()->first('email'),
            ], 422);
        }

        $email = $request->input('email');
        $lastCode = DB::table('verification_codes')
            ->where('verifiable', $email)
            ->where('created_at', '>', now()->subMinutes(2))
            ->latest('created_at')
            ->first();

        if ($lastCode) {
            $retryAfter = max(1, 120 - now()->diffInSeconds($lastCode->created_at));

            return response()->json([
                'success' => false,
                'error' => 'Повторная отправка будет доступна через ' . $retryAfter . ' сек.',
                'retry_after' => $retryAfter,
            ], 429);
        }

        try {
            VerificationCode::send($email);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }

        return response()->json([
            'success' => true,
        ]);
    }

    public function saveEmailAndSendCode(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!empty($user->email) && $user->email !== $request->input('email')) {
            return response()->json([
                'success' => false,
                'error' => 'Почта уже указана.',
            ], 409);
        }

        $validator = validator($request->all(), [
            'email' => [
                'required', 'email', 'max:255', 'ends_with:.ru',
                new DisposableEmail(),
                'unique:users,email,' . $user->id . ',id,deleted_at,NULL',
            ],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => $validator->errors()->first('email')], 422);
        }

        $email = $request->input('email');
        $previousEmail = $user->email;
        $previousPromptStage = $user->email_prompt_stage;
        $previousPromptAt = $user->next_email_prompt_at;
        $lastCode = DB::table('verification_codes')
            ->where('verifiable', $email)
            ->where('created_at', '>', now()->subMinutes(2))
            ->latest('created_at')
            ->first();

        if ($lastCode) {
            $retryAfter = max(1, 120 - now()->diffInSeconds($lastCode->created_at));

            return response()->json([
                'success' => false,
                'error' => 'Повторная отправка будет доступна через ' . $retryAfter . ' сек.',
                'retry_after' => $retryAfter,
            ], 429);
        }

        $user->forceFill([
            'email' => $email,
        ])->save();

        \App\Models\UserActivation::updateOrCreate(
            ['user_id' => $user->id, 'type' => 'email'],
            ['email' => $email, 'is_verified' => false]
        );

        try {
            VerificationCode::send($email);
        } catch (\Throwable $e) {
            $user->forceFill([
                'email' => $previousEmail,
                'email_prompt_stage' => $previousPromptStage,
                'next_email_prompt_at' => $previousPromptAt,
            ])->save();

            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }

        return response()->json([
            'success' => true,
            'email' => $user->email,
            'email_required' => false,
            'email_verification_required' => true,
            'retry_after' => 120,
        ]);
    }

    public function verifyEmailCode(Request $request): JsonResponse
    {
        $user = $request->user();
        $validator = validator($request->all(), [
            'code' => ['required', 'digits:6'],
        ]);

        if ($validator->fails() || empty($user->email)) {
            return response()->json(['success' => false, 'error' => 'Введите шестизначный код.'], 422);
        }

        if (!VerificationCode::verify($request->input('code'), $user->email, true)) {
            return response()->json(['success' => false, 'error' => 'Неверный или просроченный код.'], 422);
        }

        \App\Models\UserActivation::updateOrCreate(
            ['user_id' => $user->id, 'type' => 'email'],
            ['email' => $user->email, 'is_verified' => true]
        );
        $user->forceFill(['email_prompt_stage' => 0, 'next_email_prompt_at' => null])->save();
        $user->clearSuspiciousStatus();

        return response()->json([
            'success' => true,
            'email' => $user->email,
            'email_required' => false,
            'email_verification_required' => false,
        ]);
    }

    public function postponeEmailPrompt(Request $request): JsonResponse
    {
        $user = $request->user();

        if (empty($user->email) || $user->emailVerificationPending()) {
            $user->postponeEmailPrompt();
        }

        return response()->json([
            'success' => true,
            'next_email_prompt_at' => optional($user->next_email_prompt_at)->toIso8601String(),
            'show_email_prompt' => false,
        ]);
    }

    public function savePhoneAndSendCode(Request $request): JsonResponse
    {
        $user = $request->user();
        $validator = validator($request->all(), [
            'phone' => ['required', 'string', 'regex:/^\\+?[0-9 ()-]{10,20}$/', 'unique:users,phone,' . $user->id . ',id,deleted_at,NULL'],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => $validator->errors()->first('phone')], 422);
        }

        $phone = trim($request->input('phone'));
        if (!empty($user->phone) && $user->phone !== $phone) {
            return response()->json(['success' => false, 'error' => 'Телефон уже указан.'], 409);
        }

        $user->forceFill(['phone' => $phone, 'phone_hash' => deels_phone_hash($phone)])->save();
        $activation = \App\Models\UserActivation::updateOrCreate(
            ['user_id' => $user->id, 'type' => 'phone'],
            ['phone' => $phone, 'is_verified' => false]
        );

        try {
            $result = (new \App\Helpers\UserHelper())->sendSMSCodeVerifyPhone($activation, true, 'sms_web');
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => 'Не удалось отправить SMS. Попробуйте позже.'], 500);
        }

        if (is_array($result) && empty($result['success'])) {
            return response()->json([
                'success' => false,
                'error' => $result['message'] ?? 'Не удалось отправить SMS.',
                'limit_reached' => $result['limit_reached'] ?? false,
                'retry_after' => $result['retry_after'] ?? null,
            ], 429);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'] ?? 'SMS отправлено.',
            'retry_after' => 60,
            'attempts_left' => $result['attempts_left'] ?? null,
        ]);
    }

    public function resendPhoneCode(Request $request): JsonResponse
    {
        $user = $request->user();
        $activation = $user->phoneVerify()->first();
        if (!$activation || $activation->is_verified) {
            return response()->json(['success' => false, 'error' => 'Номер телефона не ожидает подтверждения.'], 422);
        }

        try {
            $helper = new \App\Helpers\UserHelper();
            $result = $request->input('type') === 'sms'
                ? $helper->sendSMSCodeVerifyPhone($activation, true, 'sms_web')
                : $helper->sendCodeVerifyPhone($activation, true);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => 'Не удалось отправить код. Попробуйте позже.'], 500);
        }

        if (is_array($result) && empty($result['success'])) {
            return response()->json([
                'success' => false,
                'error' => $result['message'] ?? 'Не удалось отправить код.',
                'limit_reached' => $result['limit_reached'] ?? false,
                'retry_after' => $result['retry_after'] ?? null,
            ], 429);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'] ?? 'Код отправлен.',
            'retry_after' => 60,
            'attempts_left' => $result['attempts_left'] ?? null,
        ]);
    }

    public function verifyPhoneCode(Request $request): JsonResponse
    {
        $validator = validator($request->all(), ['code' => ['required', 'digits:4']]);
        $activation = $request->user()->phoneVerify()->first();

        if ($validator->fails() || !$activation || (string) $activation->token !== (string) $request->input('code')) {
            return response()->json(['success' => false, 'error' => 'Неверный код подтверждения телефона.'], 422);
        }

        $activation->forceFill(['is_verified' => true])->save();
        $request->user()->forceFill(['phone_prompt_stage' => 0, 'next_phone_prompt_at' => null])->save();
        $request->user()->clearSuspiciousStatus();

        return response()->json(['success' => true]);
    }

    public function postponePhonePrompt(Request $request): JsonResponse
    {
        $user = $request->user();
        if (empty($user->phone) || $user->phoneVerificationPending()) {
            $user->postponePhonePrompt();
        }

        return response()->json([
            'success' => true,
            'next_phone_prompt_at' => optional($user->next_phone_prompt_at)->toIso8601String(),
            'show_phone_prompt' => false,
        ]);
    }

    public function changeAvatar(
        Request     $request,
        UserService $userService
    ): RedirectResponse
    {
        $validated = $request->validate([
            'default_avatar' => 'required|string',
            'avatar' => 'sometimes|image|max:3000',
        ]);

        $user = auth()->user();
        if (!empty($validated['avatar'])) {
            $user->update([
                'avatar' => $userService->changeAvatar($request->file('avatar')),
            ]);
        } else {
            $user->update([
                'avatar' => $validated['default_avatar'],
            ]);
        }

        return back()->with('success', 'Изображение успешно обнавлено.');
    }

    public function checkUsernameUniqueness(Request $request): JsonResponse
    {
        $validator = validator($request->all(), [
            'username' => 'required|unique:users,username,NULL,id,deleted_at,NULL',
        ]);

        return response()->json([
            'username_exists' => !$validator->fails(),
        ]);
    }

    public function removeFromMailList(Request $request)
    {
        $data = $request->input('token');
        $message = 'Вы успешно отписались от рассылки!';
        if ($data) {
            $mailing = NewsletterMail::where('token', $data)->first();
            if ($mailing) {
                $user = User::where('email', $mailing->email)->first();
                if ($user) {
                    $user->unsubscribe = true;
                    $user->save();
                }
            }
        }
        return view('newsletters.unsubscribe', compact('message'));
    }

    public function activation()
    {
        $this->data['title'] = 'Подтвердите данные';

        if (Auth::user()) {
            if (Auth::user()->is_activated) {
                return redirect()->route('dashboard');
            }
        }
        return view('auth.activation', $this->data);
    }

    public function phone_update(Request $request)
    {
        $user_id = $request->input('user_id');
        $phone = $request->input('phone');
        $user_requested = User::find($user_id);
        $user = Auth::user() ?? auth()->user() ?? $user_requested->id ?? null;


        $user_id = $user->id ?? $user_id ?? null;
        if (!$user_id) {
            return response()->json([
                'success' => false,
                'error' => 'Пользователь не найден'
            ]);
        }

        if ($user_requested) {
            $user_requested->update([
                'phone' => $phone,
                'phone_hash' => deels_phone_hash($phone),
            ]);
        }

        try {
            $activation_item = \App\Models\UserActivation::updateOrCreate(['user_id' => $user_requested->id, 'type' => 'phone', 'phone' => $user_requested->phone, 'token' => null, 'created_at' => now(), 'ip_address' => $ip ?? null]);

            $user_helper = new \App\Helpers\UserHelper;
            return $user_helper->sendSMSCodeVerifyPhone($activation_item, true, 'sms_web');
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => 'Произошла ошибка'
            ]);
        }

    }

    public function activation_resend(Request $request)
    {
        $user_id = $request->input('user_id');
        $type = $request->input('type');
        $user_requested = User::find($user_id);
        $user = Auth::user() ?? auth()->user() ?? $user_requested->id ?? null;


        $user_id = $user->id ?? $user_id ?? null;
        if (!$user_id) {
            return response()->json([
                'success' => false,
                'action' => $type,
                'error' => 'Пользователь не найден'
            ]);
        }

        if ($user_requested->is_activated) {
            return response()->json([
                'success' => false,
                'action' => $type,
                'error' => 'Пользователь активирован'
            ]);
        }

        if ($type == 'sms') {
            $phone_activation = \App\Models\UserActivation::where('user_id', $user_id)->where('type', 'phone')->first();
            $user_helper = new \App\Helpers\UserHelper;
            $response = $user_helper->sendSMSCodeVerifyPhone(
                $phone_activation,
                false,
                $request->input('source') === 'sms_web' ? 'sms_web' : 'sms'
            );
            return $response;
        }

        if ($type == 'phone') {
            $phone_activation = \App\Models\UserActivation::where('user_id', $user_id)->where('type', 'phone')->first();
            $user_helper = new \App\Helpers\UserHelper;
            $response = $user_helper->sendCodeVerifyPhone($phone_activation);
            return $response;
        }

        return response()->json([
            'success' => false,
            'action' => $type,
            'error' => 'Произошла ошибка'
        ]);

    }


    public function activation_verify(Request $request)
    {
        $user_id = $request->input('user_id');
        $phone = $request->input('phone');

        if ($user_id) {
            $user = User::find($user_id);
        } else {
            $user = Auth::user() ?? auth()->user() ?? null;
        }

        if (!$user) {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Пользователь не найден'
                ]);
            }
            return redirect()->back()->with('error', 'Пользователь не найден');
        }
        if ($phone) {
            if ($user->phone) {
                if (request()->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Телефон уже добавлен'
                    ]);
                }
                return redirect()->back()->with('error', 'Телефон уже добавлен');
            }
            $activation_data = [
                ['user_id' => $user->id, 'type' => 'phone', 'phone' => $phone, 'token' => null, 'created_at' => now()]
            ];

            foreach ($activation_data as $activation) {
                $activation_item = \App\Models\UserActivation::updateOrCreate($activation);
                if ($activation_item['type'] == 'phone') {
                    $user_helper = new \App\Helpers\UserHelper;
                    $response = $user_helper->sendSMSCodeVerifyPhone($activation_item, false, 'sms_web');
                }
            }
            return redirect()->back()->with('success', 'Телефон успешно добавлен!');
        }


        $phone_code = $request->input('phone_code');
        $verify_codes = [];
        $phone_verify = false;
        $phone_activation = \App\Models\UserActivation::where('user_id', $user->id)->where('type', 'phone')->first();

        if ($phone_activation && $phone_activation->token == $phone_code) {
            $phone_verify = true;
            $phone_activation->is_verified = true;
            $phone_activation->save();
            $user->clearSuspiciousStatus();
        } else {
            try {
                if (!$phone_activation->is_verified) {
                    $verify_codes[] = 'Неверный код подтверждения телефона';
                } else {
                    $phone_verify = true;
                }
            } catch (\Throwable $e) {
                $verify_codes[] = 'Неверный код подтверждения телефона';
            }

        }

        if ($phone_verify) {
            $wasActivated = (bool) $user->is_activated;
            $user->is_activated = true;
            $user->save();

            if (!$wasActivated) {
                app(ReferralBonusService::class)->awardForRegistration($user);
            }
        } else {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Подтверждение не выполнено!',
                ]);
            }

            return redirect()->back()->with('error', 'Подтверждение не выполнено!<br>' . implode('<br>', $verify_codes));
        }

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
            ]);
        }

        return redirect()->route('home')->with('success', 'Подтверждение выполнено!');
    }

}
