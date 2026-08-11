<?php

declare(strict_types=1);

return [
    'username' => env('CDNVIDEO_USERNAME'),
    'password' => env('CDNVIDEO_PASSWORD'),
    'token' => env('CDNVIDEO_CLIENT_TOKEN'),
    'base_url' => env('CDNVIDEO_API_URL', 'https://api.cdnvideo.ru'),
];

