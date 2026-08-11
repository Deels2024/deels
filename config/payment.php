<?php

declare(strict_types=1);

return [
    'tinkoff' => [
        'api_url' => env('TINKOFF_EACQ_API_URL', 'https://securepay.tinkoff.ru/'),
        'terminal' => env('TINKOFF_EACQ_TERMINAL', '1619081031059'),
        'terminal_secret' => env('tinkoff_eacq_terminal', 'i0hikbqorpis86rw'),
    ],
];
