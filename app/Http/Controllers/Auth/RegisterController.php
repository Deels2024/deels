<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Helpers\UserHelper;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\DisposableEmail;
use App\Services\RegistrationCustomFieldService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use NextApps\VerificationCode\VerificationCode;

class RegisterController extends Controller
{
    private const FOREIGN_EMAIL_REGISTRATION_MESSAGE = 'Регистрация через иностранные почтовые сервисы (включая Gmail, iCloud, Outlook) временно недоступна в соответствии с законодательством РФ. Пожалуйста, укажите почту с российским доменом (Яндекс, Mail.ru, Рамблер и др.) или воспользуйтесь входом ниже через Яндекс, Вконтакте, Mail.ru или Одноклассники';

    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    public function register(Request $request)
    {
        $customfield = app(RegistrationCustomFieldService::class);

        if ($customfield->isTripped($request)) {
            $customfield->ban($request);

            return redirect()->route('banned');
        }

        if ($customfield->isBanned($request)) {
            return redirect()->route('banned');
        }

        $request->merge([
            'phone' => $this->normalizeRussianPhone($request->input('phone')),
        ]);

        $rules = [
//            'name' => 'required|max:255',
//            'email' => ['required', 'email', 'max:255', 'ends_with:.ru', 'unique:users'],
            'username' => 'required|unique:users,username,NULL,id,deleted_at,NULL|max:60',
            'email' => ['required', 'email', 'max:255', 'ends_with:.ru', new DisposableEmail(), 'unique:users,email,NULL,id,deleted_at,NULL'],
            'code' => ['required', 'digits:6'],
            'password' => 'required|min:6|confirmed',
            'phone' => ['sometimes', 'nullable', 'regex:/^\+7\d{10}$/'],
            'agree_1' => 'required',
            'agree_2' => 'required',
            'agree_3' => 'required',
            'agree_4' => 'required',
            'registration_fill_time_ms' => ['nullable', 'integer', 'min:0'],
            'registration_keypress_count' => ['nullable', 'integer', 'min:0'],
            'registration_paste_insert_count' => ['nullable', 'integer', 'min:0'],
            'registration_browser_autofill' => ['nullable', 'boolean'],
        ];
        $validator = validator($request->all(), $rules, [
            'email.ends_with' => self::FOREIGN_EMAIL_REGISTRATION_MESSAGE,
            'phone.regex' => 'Укажите телефон в корректном формате.',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        }

//        if (!$request->get('code')) {
//            return redirect()->back()->with('error', 'Вы не ввели код подверждения!');
//        }
        $code = $request->input('code');
        $email = $request->input('email');
        $verification = VerificationCode::verify($code, $email, true);
        if (!$verification) {
            return redirect()->back()
                ->withInput($request->except('code'))
                ->withErrors(['code' => 'Неверный или просроченный код подтверждения почты.']);
        }

        if (1 == get_option('enable_recaptcha_registration')) {
//            $this->validate($request, ['g-recaptcha-response' => 'required']);
//
//            $secret = get_option('recaptcha_secret_key');
//            $gRecaptchaResponse = $request->input('g-recaptcha-response');
//            $remoteIp = $request->ip();
//
//            $recaptcha = new ReCaptcha($secret);
//            $resp = $recaptcha->verify($gRecaptchaResponse, $remoteIp);
//            if (!$resp->isSuccess()) {
//                return redirect()->back()->with('error', 'reCAPTCHA is not verified');
//            }
        }

        event(new Registered($user = $this->create($request->all(), null, $request->ip())));

        $this->guard()->login($user);

        try {
//            Mail::send(
//                [],
//                [],
//                function (Message $message) use ($user): void {
//                    $message
//                        ->from(env('MAIL_FROM_ADDRESS', 'info@deels.ru'), 'DEELS')
//                        ->to($user->email)
//                        ->subject('Успешная регистрация')
//                        ->html('<img src="https://deels.ru/email_banners/Frame 36075.jpg" style="max-width: 100%; height:auto;">', 'text/html');
//                }
//            );
        } catch (\Throwable $e) {

        }
        $redirect_path = $this->redirectPath();
        if($redirect_path == '/dashboard') {
            $redirect_path = '/home';
        }

        $redirect_path = '/dashboard';

        return $this->registered($request, $user)
            ?: redirect($redirect_path);
    }

    public function api_register(Request $request)
    {
        if ($request->filled('phone')) {
            $request->merge([
                'phone' => $this->normalizeRussianPhone($request->input('phone')),
            ]);
        }

        $rules = [
//            'name' => 'required|max:255',
//            'email' => ['required', 'email', 'max:255', 'ends_with:.ru', 'unique:users'],
            'email' => ['required', 'email', 'max:255', 'ends_with:.ru', new DisposableEmail(), 'unique:users'],
            'password' => 'required|min:6|confirmed',
//            'code' => 'required',
            'phone' => ['sometimes', 'nullable', 'regex:/^\+7\d{10}$/'],
            'registration_fill_time_ms' => ['nullable', 'integer', 'min:0'],
            'registration_keypress_count' => ['nullable', 'integer', 'min:0'],
            'registration_paste_insert_count' => ['nullable', 'integer', 'min:0'],
            'registration_browser_autofill' => ['nullable', 'boolean'],
        ];
        $validator = validator($request->all(), $rules, [
            'email.ends_with' => self::FOREIGN_EMAIL_REGISTRATION_MESSAGE,
            'phone.regex' => 'Укажите телефон в корректном формате.',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ]);
        }

        $code = $request->input('code');
        $email = $request->input('email');
//        $verification = VerificationCode::verify($code, $email);
//        if (!$verification) {
//            return response()->json([
//                'success' => false,
//                'error' => 'Неправильный код подтверждения!'
//            ]);
//        }


        $user_data = $this->create($request->all(), true, $request->ip());
        $user = $user_data['user'];
        event(new Registered($user));

        $user->tokens()->delete();
        $token = $user->createToken('mobile')->plainTextToken;

        try {
//            Mail::send(
//                [],
//                [],
//                function (Message $message) use ($user): void {
//                    $message
//                        ->from(env('MAIL_FROM_ADDRESS', 'info@deels.ru'), 'DEELS')
//                        ->to($user->email)
//                        ->subject('Успешная регистрация')
//                        ->html('<img src="https://deels.ru/email_banners/Frame 36075.jpg" style="max-width: 100%; height:auto;">', 'text/html');
//                }
//            );

        } catch (\Throwable $e){

        }

        $data = [
            'success' => true,
            'user_id' => $user->id,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ];
        if(!empty($user_data['data'])) {
            foreach ($user_data['data'] as $key => $value) {
                $data[$key] = $value;
            }
        }
        return response()->json($data);
    }

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/dashboard';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware('guest');
        $this->middleware('throttle:registrations')
            ->only(['register', 'api_register']);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|max:255',
            'email' => ['required', 'email', 'max:255', new DisposableEmail(), 'unique:users'],
            'password' => 'required|min:6|confirmed',
        ]);
    }

    protected function create(array $data, $api = false, $ip = null)
    {
        $referal = \Cookie::get('refCode') ?? request('refCode') ?? null;
        $phone = $this->normalizeRussianPhone($data['phone'] ?? null);

        $new_user = User::create([
            'name' => $data['name'] ?? null,
            'email' => $data['email'],
            'username' => $data['username'] ?? null,
            'password' => bcrypt($data['password']),
            'user_type' => 'user',
            'phone' => $phone,
            'phone_hash' => deels_phone_hash($phone),
            'active_status' => 1,
            'is_activated' => 0,
            'referral_code' => Str::uuid()->toString(),
            'invite_referral_code' => $referal,
            'ip_address' => $ip,
            'need_action_at' => now()->addHours(2),
            'is_suspicious' => $this->isSuspiciousRegistration($data),
        ]);

        $activation_data = [];

        if ($new_user->phone) {
            $activation_data[] = [
                'user_id' => $new_user->id,
                'type' => 'phone',
                'phone' => $new_user->phone,
                'token' => null,
                'created_at' => now(),
                'ip_address' => $ip ?? null,
            ];
        }


        foreach ($activation_data as $activation) {
            $activation_item = \App\Models\UserActivation::updateOrCreate($activation);
            if ($activation_item['type'] == 'phone') {
                $user_helper = new \App\Helpers\UserHelper;
                $response = $user_helper->sendSMSCodeVerifyPhone($activation_item, true);
                if (request()->wantsJson() || $api) {
                    return [
                        'user' => $new_user,
                        'data' => $response
                    ];
                }
            }

        }

        if ($api) {
            return ['user' => $new_user, 'data' => []];
        }

        return $new_user;
    }

    protected function isSuspiciousRegistration(array $data): bool
    {
        $fillTimeMissing = !array_key_exists('registration_fill_time_ms', $data)
            || $data['registration_fill_time_ms'] === null
            || $data['registration_fill_time_ms'] === '';
        $filledTooQuickly = $fillTimeMissing || (int) $data['registration_fill_time_ms'] < 5000;
        $hasFewKeypresses = (int) ($data['registration_keypress_count'] ?? 0) < 3;
        $hasPasteOrInsert = (int) ($data['registration_paste_insert_count'] ?? 0) > 0;
        $hasBrowserAutofill = filter_var(
            $data['registration_browser_autofill'] ?? false,
            FILTER_VALIDATE_BOOLEAN
        );

        return $filledTooQuickly
            && $hasFewKeypresses
            && !$hasPasteOrInsert
            && !$hasBrowserAutofill;
    }

    private function normalizeRussianPhone(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone);

        if ($digits === '') {
            return null;
        }

        if (strlen($digits) === 11 && ($digits[0] === '7' || $digits[0] === '8')) {
            $digits = '7' . substr($digits, 1);
        } elseif (strlen($digits) === 10) {
            $digits = '7' . $digits;
        }

        return '+' . $digits;
    }

    public function checkEmailUniqueness(Request $request): JsonResponse
    {
        $validator = validator($request->all(), [
            'email' => ['bail', 'required', 'email', new DisposableEmail(), 'unique:users,email,NULL,id,deleted_at,NULL'],
        ]);

        return response()->json([
            'email_exists' => !$validator->fails(),
            'error' => $validator->errors()->first('email') ?: null,
        ]);
    }

    public function checkEmailCode(Request $request): JsonResponse
    {
        $validator = validator($request->all(), [
            'email' => ['required', 'email'],
            'code' => ['required', 'digits:6'],
        ]);

        if ($validator->fails()) {
            return response()->json(['valid' => false]);
        }

        return response()->json([
            'valid' => VerificationCode::verify(
                $request->input('code'),
                $request->input('email'),
                false
            ),
        ]);
    }


}
