<?php

namespace App\Services;

use Bavix\Wallet\Internal\Service\MathServiceInterface;
use Bavix\Wallet\Services\ExchangeServiceInterface;

/**
 *
 */
class CoinExchangeService implements ExchangeServiceInterface
{
    /**
     * @var array|array[]
     */
    private array $rates = [
        'COIN' => [
            'RUB' => 0.01,
        ],
    ];

    /**
     * @var MathServiceInterface
     */
    private MathServiceInterface $mathService;

    /**
     * @param MathServiceInterface $mathService
     */
    public function __construct(MathServiceInterface $mathService)
    {
        $this->mathService = $mathService;

        foreach ($this->rates as $from => $rates) {
            foreach ($rates as $to => $rate) {
                if (empty($this->rates[$to][$from])) {
                    $this->rates[$to][$from] = $this->mathService->div(1, $rate);
                }
            }
        }
    }

    /** @param float|int|string $amount */
    public function convertTo(string $fromCurrency, string $toCurrency, $amount): string
    {
        return $this->mathService->mul($amount, $this->rates[$fromCurrency][$toCurrency] ?? 1);
    }
}