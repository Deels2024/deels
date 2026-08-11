<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Setting Image Size
    |--------------------------------------------------------------------------
    | [width X height]
    |
    | Thumbnail (default 250px x 250px)
    | Feature Image (default 500px x 350px)
    | Original image resolution (unmodified)
    |
    */

    'size' => [
        'thumbnail' => [400, 400],
        'feature_image' => [1460, 1024], // Main Product
        // 'full' => null,
    ],

    'stories' => [
        'max_upload_mb' => env('STORY_MAX_UPLOAD_MB', 100),
        'image_max_upload_mb' => env('STORY_IMAGE_MAX_UPLOAD_MB', 10),
        'max_video_duration_seconds' => 60,
        'image_max_width' => 1920,
        'image_max_height' => 1080,
        'image_allowed_extensions' => ['heif', 'heic', 'jpeg', 'jpg', 'png'],
        'image_unsupported_extensions' => [
            'raw', 'dng', 'cr2', 'cr3', 'nef', 'nrw', 'arw', 'srf', 'sr2',
            'orf', 'rw2', 'raf', 'pef', 'srw', 'x3f', 'erf', 'kdc', 'dcr',
            'mos', 'mrw', 'iiq', '3fr',
        ],
        'video_allowed_mime_types' => [
            'application/octet-stream',
            'video/quicktime',
            'video/mp4',
            'video/mov',
            'video/avi',
            'video/mpeg',
            'video/webm',
            'video/x-msvideo',
        ],
    ],
];
