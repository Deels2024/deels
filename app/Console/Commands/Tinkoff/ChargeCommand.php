<?php

declare(strict_types=1);

namespace App\Console\Commands\Tinkoff;

use App\TinkoffService;
use Illuminate\Console\Command;

class ChargeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:charge';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $api = new TinkoffService(
            '1619081031059',
            'i0hikbqorpis86rw'
        );

        $params = [
            'TerminalKey' => '1619081031059',
            'OrderId' => time(),
            'Amount' => 1 * 100,
            'Taxation' => 'usn_income',
            'Receipt' => [
                'Taxation' => 'usn_income',
                'Email' => 'ssn.work@mail.ru',
                'Items' => [
                    [
                        'Name' => " 'Донат в копилку ",
                        'Price' => 1 * 100,
                        'Quantity' => 1,
                        'Amount' => 1 * 100,
                        'PaymentMethod' => 'full_payment',
                        'PaymentObject' => 'commodity',
                        'Tax' => 'none',
                    ],
                ],
            ],
            'DATA' => [
                'Email' => 'ssn.work@mail.ru',
                'Connection_type' => 'example',
            ],
        ];

        $init = json_decode($api->init($params));

        dd($api->charge([
            'TerminalKey' => '1619081031059',
            'PaymentId' => $init->PaymentId,
            'RebillId' => '850132253',
        ]));
    }
}
