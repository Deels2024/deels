<?php

declare(strict_types=1);

namespace App\Services\Home;

use App\Models\User;
use Bavix\Wallet\Models\Transaction;

final class DeelsBankService
{
    private const INITIAL_BALANCE = 10000000;

    public function balance(): int
    {
        $legacyTransactions = (int) Transaction::where(
            'meta',
            'like',
            '%"get":"coins","old_connected"%'
        )->sum('amount');

        $bankUser = User::where('email', 'moderdeels@mail.ru')->first();
        $balance = $bankUser
            ? (int) ($bankUser->wallet_balance ?? 0) - $legacyTransactions
            : self::INITIAL_BALANCE - $legacyTransactions;

        return max(0, $balance);
    }

    public function formattedBalance(): string
    {
        return str_pad((string) $this->balance(), 8, '0', STR_PAD_LEFT);
    }
}
