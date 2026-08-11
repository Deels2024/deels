<?php

declare(strict_types=1);

namespace App\Models;

use App\Helpers\AppHelper;
use App\Notifications\ResetPasswordNotification;
use App\Services\ProjectWalletService;
use App\Traits\Referral;
use Bavix\Wallet\Exceptions\InsufficientFunds;
use Bavix\Wallet\Interfaces\Customer;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Interfaces\WalletFloat;
use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Traits\CanPay;
use Bavix\Wallet\Traits\HasWalletFloat;
use Bavix\Wallet\Traits\HasWallets;
use Carbon\Carbon;
use Cmgmyr\Messenger\Traits\Messagable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\HasApiTokens;
use Overtrue\LaravelFollow\Traits\Followable;
use Overtrue\LaravelFollow\Traits\Follower;
use Illuminate\Database\Eloquent\SoftDeletes;


class User extends Authenticatable implements Wallet, WalletFloat, Customer
{
    use Notifiable;
    use Messagable;
    use HasApiTokens;
    use HasWalletFloat;
    use HasWallets;
    use Follower;
    use Followable;
    use CanPay;
    use SoftDeletes;
    use Referral;

    /** @var array */
    protected $guarded = [];

    /** @var array */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'limits' => 'array',
        'meta_data' => 'array',
        'checked_at' => 'datetime',
        'need_action_at' => 'datetime',
        'trust_rating' => 'integer',
        'first_message_followings_only' => 'boolean',
        'next_email_prompt_at' => 'datetime',
        'email_prompt_stage' => 'integer',
        'next_phone_prompt_at' => 'datetime',
        'phone_prompt_stage' => 'integer',
        'is_suspicious' => 'boolean',
        'suspicious_violations' => 'integer',
        'suspicious_moderation_pending' => 'boolean',
        'suspicious_moderation_requested_at' => 'datetime',
        'suspicious_blocked_until' => 'datetime',
    ];

    protected $appends = array('avatar_url', 'fullname', 'wallet_balance', 'withdraw_balance', 'profit_balance');

    public function routeNotificationForFcm()
    {
        return $this->device_name;
    }

    public function events(): HasMany
    {
        return $this->hasMany(UserEvent::class);
    }

    public function clearSuspiciousStatus(): void
    {
        if (! $this->is_suspicious && ! $this->suspicious_moderation_pending && ! $this->suspicious_blocked_until) {
            return;
        }

        $this->forceFill([
            'is_suspicious' => false,
            'suspicious_violations' => 0,
            'suspicious_moderation_pending' => false,
            'suspicious_moderation_requested_at' => null,
            'suspicious_blocked_until' => null,
        ])->save();
    }

    public function shouldShowEmailPrompt(): bool
    {
        if (!empty($this->email)) {
            if (!$this->emailVerificationPending()) {
                return false;
            }
        }

        $dueAt = $this->emailPromptDueAt();

        return $dueAt !== null && $dueAt->lte(now());
    }

    public function emailVerificationPending(): bool
    {
        if (empty($this->email)) {
            return false;
        }

        $emailVerification = $this->emailVerify()->first();

        return $emailVerification !== null && !$emailVerification->is_verified;
    }

    public function emailPromptDueAt(): ?Carbon
    {
        return $this->next_email_prompt_at
            ?: ($this->created_at ? $this->created_at->copy()->addDay() : null);
    }

    public function postponeEmailPrompt(): void
    {
        $stage = (int) $this->email_prompt_stage;
        $registeredAt = $this->created_at ? $this->created_at->copy() : now();

        if ($stage === 0) {
            $nextPromptAt = $registeredAt->addDays(3);
            if ($nextPromptAt->lte(now())) {
                $nextPromptAt = now()->addDays(3);
            }
        } elseif ($stage === 1) {
            $nextPromptAt = $registeredAt->addWeek();
            if ($nextPromptAt->lte(now())) {
                $nextPromptAt = now()->addWeek();
            }
        } else {
            $nextPromptAt = now()->addMonthNoOverflow();
        }

        $this->forceFill([
            'email_prompt_stage' => min($stage + 1, 3),
            'next_email_prompt_at' => $nextPromptAt,
        ])->save();
    }

    public function shouldShowPhonePrompt(): bool
    {
        if (!empty($this->phone) && !$this->phoneVerificationPending()) {
            return false;
        }

        $dueAt = $this->phonePromptDueAt();

        return $dueAt !== null && $dueAt->lte(now());
    }

    public function phoneVerificationPending(): bool
    {
        if (empty($this->phone)) {
            return false;
        }

        $phoneVerification = $this->phoneVerify()->first();

        return $phoneVerification === null || !$phoneVerification->is_verified;
    }

    public function phonePromptDueAt(): ?Carbon
    {
        return $this->next_phone_prompt_at
            ?: ($this->created_at ? $this->created_at->copy()->addDay() : null);
    }

    public function postponePhonePrompt(): void
    {
        $stage = (int) $this->phone_prompt_stage;
        $registeredAt = $this->created_at ? $this->created_at->copy() : now();

        if ($stage === 0) {
            $nextPromptAt = $registeredAt->addDays(3);
            if ($nextPromptAt->lte(now())) {
                $nextPromptAt = now()->addDays(3);
            }
        } elseif ($stage === 1) {
            $nextPromptAt = $registeredAt->addWeek();
            if ($nextPromptAt->lte(now())) {
                $nextPromptAt = now()->addWeek();
            }
        } else {
            $nextPromptAt = now()->addMonthNoOverflow();
        }

        $this->forceFill([
            'phone_prompt_stage' => min($stage + 1, 3),
            'next_phone_prompt_at' => $nextPromptAt,
        ])->save();
    }

    public function my_campaigns()
    {
        return $this->hasMany(Campaign::class);
    }


    public function setConnectTokenAttribute($value)
    {
        if($value) {
            $token = $value;
        } else {
            $token =  md5(rand(1, 10) . microtime());
        }

        $this->attributes['connect_token'] = $token;
    }

    public function getUsernameAttribute($value)
    {
        if($value) {
            $username = $value;
        } else {
            if($this->id != 0) {
                if($this->email) {
                    $username = substr($this->email, 0, strrpos($this->email, '@'));
                } else {
                    $username = 'username_'.$this->id;
                }

                $this->username = $username;
                $this->save();
            } else {
                $username = 'deleted_user';
                $this->username = 'deleted_user';
            }

        }

        return $username;
    }



    public function avatar()
    {
        $men = ["/default_avatars/avatar_1.png", "/default_avatars/avatar_2.png"];
        $women = [
            "/default_avatars/avatar_3.png", "/default_avatars/avatar_4.png", "/default_avatars/avatar_5.png", "/default_avatars/avatar_6.png",
        ];

        $num = ($this->id);

        if ($this->avatar) {
            return $this->avatar;
        }
        if ($this->gender === 'female') {
            return $women[($num % 4)];
        }

        return $men[($num % 2)];
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function phoneVerify()
    {
        return $this->hasOne(UserActivation::class)->where('type', 'phone');
    }

    public function emailVerify()
    {
        return $this->hasOne(UserActivation::class)->where('type', 'email');
    }

    public function getPhoneVerifyDate()
    {
        $verify = $this->phoneVerify;
        if(!empty($verify)) {
            return Carbon::parse($this->phoneVerify->updated_at)->format('d.m.Y H:i');
        }
        return '-';
    }



    public function signed_up_datetime()
    {
        return $this->created_at->timezone(get_option('default_timezone'))
            ->format(get_option('date_format_custom') . ' ' . get_option('time_format_custom'));
    }

    public function status_context(): string
    {
        $status = $this->active_status;

        $context = '';
        switch ($status) {
            case '0':
                $context = 'В ожидании';
                break;
            case '1':
                $context = 'Активен';
                break;
            case '2':
                $context = 'Заблокирован';
                break;
        }

        return $context;
    }

    public function is_admin(): bool
    {
        return 'admin' === $this->user_type;
    }

    public function canReceiveFirstMessageFrom(User $sender): bool
    {
        if ($this->id === $sender->id) {
            return true;
        }

        return !$this->first_message_followings_only || $this->isFollowing($sender);
    }

    public function is_comment_admin(): bool
    {
        return 'comment' === $this->user_type;
    }

    public function is_campaign_admin(): bool
    {
        return 'campaign' === $this->user_type;
    }

    public function contributed_amount()
    {
//        $payments = Payment::whereUserId($this->id)->whereStatus('success')->sum('amount');
        $transactions = \App\Models\Transaction::where('payable_id', $this->id)->where('meta', 'like', '%{"donate"%')->sum('amount');
        try {
            return number_format(abs(intval($transactions)), 2, '.', '.');
        } catch (\Throwable $e) {
            return $transactions;
        }
    }

    public function medias(): HasMany
    {
        return $this->hasMany(Media::class);
    }

    public function withdrawal_preferences(): HasOne
    {
        return $this->hasOne(WithdrawalPreference::class);
    }

    public function backed_campaigns(): BelongsToMany
    {
        return $this->belongsToMany(Campaign::class, 'payments');
    }

    public function backed_payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function sendPasswordResetNotification($token): void
    {
        $url = 'https://deels.ru/reset-password?token=' . $token;

        $helper = new AppHelper();
        $ip = request()->ip();
        $helper->write_log($ip, 'password_reset', 'Запрос восстановления пароля пользователя '.$this->username.' (ID '.$this->id.')');
        $this->notify(new ResetPasswordNotification($url));
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(User::class, 'invite_referral_code', 'referral_code');
    }

    public function referral(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invite_referral_code', 'referral_code');
    }

    public function rewards(): HasMany
    {
        return $this->hasMany(Reward::class);
    }

    public function stories(): HasMany
    {
        return $this->hasMany(Story::class);
    }

    public function challenges(): HasMany
    {
        return $this->hasMany(Challenge::class);
    }

    public function isActiveAuthor(): bool
    {
        $activeSince = now()->subDays(7);

        return $this->stories()
            ->active()
            ->where('stories.created_at', '>=', $activeSince)
            ->exists()
            || $this->referrals()
                ->where('users.created_at', '>=', $activeSince)
                ->where('users.is_activated', true)
                ->exists();
    }

    public function getAvatarUrlAttribute()
    {
        $avatar = $this->avatar();

        if (filter_var($avatar, FILTER_VALIDATE_URL)) {
            return $avatar;
        }

        return url($avatar);
    }

    public function getFullNameAttribute()
    {
        return $this->username ?? $this->name;
    }

    public function getUserData() {
        $user_data = [
            'id' => $this->id,
            'name' => $this->name ?? '',
            'fullname' => $this->fullname ?? '',
            'username' => $this->username ?? '',
            'trust_rating' => $this->trust_rating ?? 0,
            'avatar' => url($this->avatar()),
            'avatar_url' => url($this->avatar()),
        ];

        return $user_data;
    }

    public function ratingUp(int $value): int
    {
        $this->increment('trust_rating', abs($value));
        $this->refresh();

        return $this->trust_rating;
    }

    public function ratingDown(int $value): int
    {
        $this->decrement('trust_rating', abs($value));
        $this->refresh();

        return $this->trust_rating;
    }



    public function hasThread($user_id) {
        $userIds = [$this->id, $user_id];
        $thread = Thread::whereJsonContains('users', $userIds)->first();

        return $thread;
    }

    public function blockedBy($user_id) {
        $abuse = Abuse::where('user_id', $this->id)->where('abused_by', $user_id)->where('confirmed', 1)->first();
        if($abuse) {
            return true;
        }
        return false;
    }

    public function getRepostTo(){

        $userId = $this->id;
        $followingIds = $this->followings()->pluck('followable_id')->toArray();
        $threads = \App\Models\Thread::forUser($userId)->get()
            ->pluck('users')
            ->map(function($users) use ($userId) {
                // Filter other users
                return collect($users)->filter(function($user) use ($userId) {
                    return $user != $userId && $user != 0 && $user != "0";
                });
            })   ->flatten()
            ->unique()
            ->values()->toArray();

        $ids = array_merge($followingIds,$threads);

        $users = User::whereIn('id', $ids)->get();
        return $users;
    }


    public function getWalletBalanceAttribute(){;
        $payments_wallet = $this->getWallet('payments');
        if(!$payments_wallet) {
            $this->createWallet([
                'name' => 'Payments',
                'slug' => 'payments',
                'meta' => ['currency' => 'COINS'],
            ]);
        }
        $balance = intval($payments_wallet->balance ?? 0);

        return $balance;
    }

    public function getProfitBalanceAttribute(){
        $default_wallet = $this->getWallet('default');
        $balance = round(intval($default_wallet->balance ?? 0)/100, 1);

        return $balance;
    }



    public function getWithdrawBalanceAttribute(){
        $balance = 0;
        if($this->wallets) {
            foreach ($this->wallets as $wallet) {
                $balance = $balance+intval($wallet->balance/100);
            }
        }

        return $balance;
    }

    public function wallet_withdraw($amount, $description = [], $check_only = false, int $commissionAmount = 0, string $commissionDescription = 'Комиссия сервиса')
    {

        $payments_wallet = $this->getWallet('payments');
//        $default_wallet = $this->getWallet('default');
//        $ammount_to_exchange = 0;
        $payments_wallet_balance = 0;
        if($payments_wallet) {
            $payments_wallet_balance = intval($payments_wallet->balance);
        }

        $amount = intval($amount);
        $commissionAmount = intval($commissionAmount);

        if ($commissionAmount < 0 || $commissionAmount > $amount) {
            throw new InsufficientFunds(
                'Некорректная сумма комиссии!',
                ExceptionInterface::INSUFFICIENT_FUNDS
            );
        }

        if(!$payments_wallet || $payments_wallet_balance < $amount) {
            throw new InsufficientFunds(
                'Недостаточно средств!',
                ExceptionInterface::INSUFFICIENT_FUNDS
            );
        }
        if(!$check_only) {
            try {
                $description['balance_before'] = $payments_wallet_balance;
            } catch (\Throwable $e) {

            }
            $withdrawAmount = $amount - $commissionAmount;

            if ($withdrawAmount > 0) {
                $payments_wallet->withdraw($withdrawAmount, $description);
            }

            if ($commissionAmount > 0) {
                app(ProjectWalletService::class)->collectCommission($payments_wallet, $commissionAmount, $description, $commissionDescription);
            }
        }

        return true;

//
//        if($payments_wallet_balance > 0) {
//            if(($payments_wallet_balance - $amount) <= 0) {
//                $ammount_to_exchange = $payments_wallet_balance;
//                $payments_wallet->exchange($default_wallet, $ammount_to_exchange);
//            } else {
//                $payments_wallet->exchange($default_wallet, $ammount_to_exchange);
//            }
//        }
//
//
//        $this->withdraw(intval($amount), $description);
//
//        return true;
    }

    public function manual_withdraw($amount, $description)
    {

        if($this->withdraw_balance < $amount) {
            throw new InsufficientFunds(
                'Недостаточно средств!',
                ExceptionInterface::INSUFFICIENT_FUNDS
            );
        }
        $amount = $amount*100;
        $payments_wallet = $this->getWallet('payments');
        $default_wallet = $this->getWallet('default');
        $ammount_to_exchange = 0;
        $payments_wallet_balance = 0;
        if($payments_wallet) {
            $payments_wallet_balance = intval($payments_wallet->balance);
        }
        if($payments_wallet_balance > 0) {
            if(($payments_wallet_balance - $amount) <= 0) {
                $ammount_to_exchange = $payments_wallet_balance;
                $payments_wallet->exchange($default_wallet, $ammount_to_exchange);
            } else {
                $payments_wallet->exchange($default_wallet, $ammount_to_exchange);
            }
        }


        try {
            $description['balance_before'] = $payments_wallet_balance;
        } catch (\Throwable $e) {

        }


        $amount = $amount/100;
        $this->withdraw(intval($amount), $description);

        return true;
    }
}
