<?php

declare(strict_types=1);

namespace App\Models;

use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Internal\Service\MathServiceInterface;
use Bavix\Wallet\Models\Wallet as WalletModel;
use Bavix\Wallet\Services\CastServiceInterface;
use function config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Class Transaction.
 *
 * property string      $payable_type
 * property int|string  $payable_id
 * property int         $wallet_id
 * property string      $uuid
 * property string      $type
 * property string      $amount
 * property int         $amountInt
 * property string      $amountFloat
 * property bool        $confirmed
 * property array       $meta
 * property Wallet      $payable
 * property WalletModel $wallet
 *
 * method int getKey()
 */
class Transaction extends \Bavix\Wallet\Models\Transaction
{
    public const TYPE_DEPOSIT = 'deposit';

    public const TYPE_WITHDRAW = 'withdraw';

    protected $appends = array('description');

    /**
     * var string[]
     */
    protected $fillable = [
        'payable_type',
        'payable_id',
        'wallet_id',
        'uuid',
        'type',
        'amount',
        'confirmed',
        'meta',
        'created_at',
        'updated_at',
    ];

    /**
     * var array<string, string>
     */
    protected $casts = [
        'wallet_id' => 'int',
        'confirmed' => 'bool',
        'meta' => 'json',
    ];

    protected function asJson($value)
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    public function getTable(): string
    {
        if ((string)$this->table === '') {
            $this->table = config('wallet.transaction.table', 'transactions');
        }

        return parent::getTable();
    }

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(config('wallet.wallet.model', WalletModel::class));
    }

    public function getAmountIntAttribute(): int
    {
        return (int)$this->amount;
    }

    public function getDescriptionAttribute() {
        return $this->getDescription();
    }
    public function getAmountFloatAttribute(): string
    {
        $math = app(MathServiceInterface::class);
        $decimalPlacesValue = app(CastServiceInterface::class)
            ->getWallet($this->wallet)
            ->decimal_places;
        $decimalPlaces = $math->powTen($decimalPlacesValue);

        return $math->div($this->amount, $decimalPlaces, $decimalPlacesValue);
    }

    public function setAmountFloatAttribute(float|int|string $amount): void
    {
        $math = app(MathServiceInterface::class);
        $decimalPlacesValue = app(CastServiceInterface::class)
            ->getWallet($this->wallet)
            ->decimal_places;
        $decimalPlaces = $math->powTen($decimalPlacesValue);

        $this->amount = $math->round($math->mul($amount, $decimalPlaces));
    }

    public function getDescription()
    {
        $transaction_meta = $this->meta ?? [];
        if ($transaction_meta && !is_array($transaction_meta)) {
            $transaction_meta = json_decode($transaction_meta, true);
        }

        if (isset($transaction_meta['donate'])) {
            switch ($transaction_meta['donate']) {
                case 'campaign':
                    $description = $transaction_meta['description'] ?? 'Списание за вклад в копилку';
                    break;
                case 'story':
                    $description = $transaction_meta['description'] ?? 'Оплата сторис';
                    break;
                case 'stream':
                    $description = $transaction_meta['description'] ?? 'Оплата за стрим';
                    break;
                default:
                    $description = $transaction_meta['description'] ?? '';
                    break;
            }
        } elseif (isset($transaction_meta['get'])) {
            switch ($transaction_meta['get']) {
                case 'campaign':
                    $description = 'Начисление за вклад в копилку';
                    break;
                case 'coins':
                    $description = $transaction_meta['description'] ?? 'Начисление за онлайн';
                    break;
                case 'story':
                    $description = 'Начисление за оплату сторис';
                    break;
                default:
                    $description = $transaction_meta['description'] ?? '';
                    break;
            }
        } else {
            $description = isset($this->total_amount) ? 'Запрос на вывод средств' : ($this->type == 'withdraw' ? ($transaction_meta['description'] ?? 'Транзакция') : 'Пополнение');
        }

        return $description;
    }

    public function getAmount($roubles = false)
    {
        $transaction_meta = $this->meta ?? [];
        $amount = $this->amount;
        if ($transaction_meta && !is_array($transaction_meta)) {
            $transaction_meta = json_decode($transaction_meta, true);
        }

        $currency = '<img src="/dist/img/deels_cur.svg" class="small_coin">';

        if((isset($transaction_meta['donate']) && $transaction_meta['donate'] == 'withdraw') || $roubles) {
            $amount = number_format(intval($this->amount)/100, 1, ',', ',');
            $currency = ' <span class="ruble-sign">₽</span>';
        } else {
            if(isset($transaction->total_amount)) {
                $amount = '-'.number_format($this->total_amount, 1, ',', ',');
            } else {
                $amount = number_format(intval($this->amount), 1, ',', ',');
            }
        }

        return $amount.$currency;
    }
}
