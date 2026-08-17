<?php

declare(strict_types=1);

return [
    /*
    | Keep the existing homepage as the safe default. Once home-v2.blade.php
    | is deployed and verified, the new facade can be enabled without
    | changing routes or backend contracts.
    */
    'use_v2' => (bool) env('HOME_DESIGN_V2', false),

    'cache_ttl_seconds' => (int) env('HOME_CACHE_TTL_SECONDS', 300),

    'limits' => [
        'stories' => 10,
        'challenges' => 10,
        'battles' => 6,
        'campaigns' => 8,
        'directions' => 6,
    ],
];
