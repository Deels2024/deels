<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AccountLoginRateLimiter;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    public function login(Request $request)
    {
        $this->validateLogin($request);

        if (1 == get_option('enable_recaptcha_login')) {
            $this->validate($request, ['g-recaptcha-response' => 'required']);

            $secret             = get_option('recaptcha_secret_key');
            $gRecaptchaResponse = $request->input('g-recaptcha-response');
            $remoteIp           = $request->ip();

            $recaptcha = new \ReCaptcha\ReCaptcha($secret);
            $resp      = $recaptcha->verify($gRecaptchaResponse, $remoteIp);
            if (!$resp->isSuccess()) {
                return redirect()->back()->with('error', 'reCAPTCHA is not verified');
            }
        }

        // Check if active account
        $user = User::where($this->loginField($request), $request->input($this->username()))->first();
        if ($user) {
            if (1 != $user->active_status) {
                return redirect()->back()->with('error', trans('app.user_account_wrong'));
            }
        }

        $rateLimiter = app(AccountLoginRateLimiter::class);
        if ($user && ($seconds = $rateLimiter->blockedFor($user)) > 0) {
            throw $this->lockoutException($request, $seconds);
        }

        if ($this->attemptLogin($request)) {
            $rateLimiter->clearFailures($user);

            return $this->sendLoginResponse($request);
        }

        if ($user && ($seconds = $rateLimiter->recordFailure($user)) > 0) {
            throw $this->lockoutException($request, $seconds);
        }

        return $this->sendFailedLoginResponse($request);
    }

    public function redirectTo()
    {
        return request('redirect', $this->redirectTo).'?action=1';
    }

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/dashboard';

    /**
     * Build credentials for either an email address or a username.
     */
    protected function credentials(Request $request): array
    {
        return [
            $this->loginField($request) => $request->input($this->username()),
            'password' => $request->input('password'),
        ];
    }

    private function loginField(Request $request): string
    {
        return filter_var($request->input($this->username()), FILTER_VALIDATE_EMAIL)
            ? 'email'
            : 'username';
    }

    private function lockoutException(Request $request, int $seconds): ValidationException
    {
        $minutes = max(1, (int) ceil($seconds / 60));
        $durationText = $minutes . ' ' . trans_choice('numbers.minutes', $minutes);

        if ($minutes >= 60) {
            $hours = intdiv($minutes, 60);
            $remainingMinutes = $minutes % 60;
            $durationText = $hours . ' ' . trans_choice('numbers.hours', $hours);

            if ($remainingMinutes > 0) {
                $durationText .= ' ' . $remainingMinutes . ' '
                    . trans_choice('numbers.minutes', $remainingMinutes);
            }
        }

        return ValidationException::withMessages([
            $this->username() => [trans('auth.throttle', [
                'duration' => $durationText,
            ])],
        ])->status(429);
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware('guest', ['except' => 'logout']);
    }

    public function logout(Request $request) {
        Auth::logout();
        return redirect('/');
    }
}
