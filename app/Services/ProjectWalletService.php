<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ProjectAccount;
use Bavix\Wallet\Interfaces\Wallet as WalletInterface;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Models\Wallet;

class ProjectWalletService
{
    private const ACCOUNT_SLUG = 'deels';
    private const WALLET_SLUG = 'project';

    public function account(): ProjectAccount
    {
        return ProjectAccount::query()->firstOrCreate(
            ['slug' => self::ACCOUNT_SLUG],
            [
                'name' => 'DEELS project account',
                'meta' => ['system' => true],
            ]
        );
    }

    public function wallet(): Wallet
    {
        $account = $this->account();
        $wallet = $account->getWallet(self::WALLET_SLUG);

        if (!$wallet) {
            $account->createWallet([
                'name' => 'Project',
                'slug' => self::WALLET_SLUG,
                'meta' => ['currency' => 'COINS', 'system' => true],
            ]);

            $wallet = $account->getWallet(self::WALLET_SLUG);
        }

        return $wallet;
    }

    public function depositCommission(int $amount, array $meta = [], string $description = 'Комиссия сервиса'): Transaction
    {
        $meta['description'] = $description;
        $meta['project_wallet'] = true;
        $meta['commission'] = true;
        $meta['project_account_slug'] = self::ACCOUNT_SLUG;

        return $this->wallet()->deposit($amount, $meta);
    }

    public function collectCommission(WalletInterface $source, int $amount, array $meta = [], string $description = 'Комиссия сервиса'): Transfer
    {
        $meta['description'] = $description;
        $meta['project_wallet'] = true;
        $meta['commission'] = true;
        $meta['project_account_slug'] = self::ACCOUNT_SLUG;

        return $source->exchange($this->wallet(), $amount, $meta);
    }
}
