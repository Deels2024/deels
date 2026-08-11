<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
    ],

    'ses' => [
        'key' => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => 'us-east-1',
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe' => [
        'model' => \App\Models\User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],

    'twitter' => [
        'client_id' => get_option('twitter_consumer_key'),
        'client_secret' => get_option('twitter_consumer_secret'),
        'redirect' => env('APP_URL').'login/twitter-callback',
    ],

    'google' => [
        'client_id' => get_option('google_client_id'),
        'client_secret' => get_option('google_client_secret'),
        'redirect' => env('APP_URL').'login/google-callback',
    ],

    'facebook' => [
        'client_id' => get_option('fb_app_id'),
        'client_secret' => get_option('fb_app_secret'),
        'redirect' => env('APP_URL').'login/facebook-callback',
    ],

    'vkontakte' => [
        'client_id' => env('VKONTAKTE_CLIENT_ID'),
        'client_secret' => env('VKONTAKTE_CLIENT_SECRET'),
        'service_key' => env('VKONTAKTE_SERVICE_KEY'),
        'redirect' => env('VKONTAKTE_REDIRECT_URI', rtrim(env('APP_URL'), '/').'/login/vk-callback'),
    ],

    'yandex' => [
        'client_id' => env('YANDEX_CLIENT_ID'),
        'client_secret' => env('YANDEX_CLIENT_SECRET'),
        'redirect' => env('APP_URL').'/login/yandex-callback',
    ],

    'appmetrica' => [
        'client_id' => env('APPMETRICA_CLIENTID'),
        'client_secret' => env('APPMETRICA_CLIENT_SECRET'),
        'redirect_uri' => env('APPMETRICA_REDIRECT_URI'),
        'access_token' => env('APPMETRICA_ACCESS_TOKEN'),
        'refresh_token' => env('APPMETRICA_REFRESH_TOKEN'),
        'application_id' => env('APPMETRICA_APPLICATION_ID'),
        'stat_base_url' => env('APPMETRICA_STAT_BASE_URL', 'https://api.appmetrica.yandex.com'),
        'management_base_url' => env('APPMETRICA_MANAGEMENT_BASE_URL', 'https://api.appmetrica.yandex.com'),
        'oauth_base_url' => env('APPMETRICA_OAUTH_BASE_URL', 'https://oauth.yandex.com'),
        'timeout' => (float) env('APPMETRICA_TIMEOUT', 15),
    ],

    'google_sheets' => [
        'credentials_path' => env('GOOGLE_SHEETS_CREDENTIALS_PATH'),
        'metrics_spreadsheet_id' => env('GOOGLE_METRICS_SPREADSHEET_ID'),
        'metrics_sheet_name' => env('GOOGLE_METRICS_SHEET_NAME', 'Статистика'),
        'timeout' => (float) env('GOOGLE_SHEETS_TIMEOUT', 20),
    ],

    'yandex_metrika' => [
        'access_token' => env('YANDEX_METRIKA_ACCESS_TOKEN'),
        'counter_id' => env('YANDEX_METRIKA_COUNTER_ID'),
        'base_url' => env('YANDEX_METRIKA_BASE_URL', 'https://api-metrika.yandex.net'),
        'timeout' => (float) env('YANDEX_METRIKA_TIMEOUT', 15),
    ],

    'mailru' => [
        'client_id' => env('MAILRU_CLIENT_ID'),
        'client_secret' => env('MAILRU_CLIENT_SECRET'),
        'redirect' => env('APP_URL').'/login/mailru-callback',
    ],

    'odnoklassniki' => [
        'client_id' => env('ODNOKLASSNIKI_CLIENT_ID'),
        'client_public' => env('ODNOKLASSNIKI_CLIENT_PUBLIC'),
        'client_secret' => env('ODNOKLASSNIKI_CLIENT_SECRET'),
        'redirect' => env('APP_URL').'/login/ok-callback',
    ],

    'tinkoff' => [
        'terminal_key' => env('TINKOFF_TERMINAL_KEY', '1619081031059'),
        'secret_key' => env('TINKOFF_SECRET_KEY', 'i0hikbqorpis86rw'),
    ],
];
