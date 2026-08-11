<?php

declare(strict_types=1);

namespace App\Services;

class UserAgentParser
{
    public function browser(?string $userAgent): string
    {
        if ($userAgent) {
            if (strpos($userAgent, 'MSIE') !== false || strpos($userAgent, 'Trident') !== false) {
                return 'Internet Explorer';
            }

            if (strpos($userAgent, 'Firefox') !== false) {
                return 'Firefox';
            }

            if (strpos($userAgent, 'Chrome') !== false) {
                return 'Chrome';
            }

            if (strpos($userAgent, 'Safari') !== false) {
                return 'Safari';
            }

            if (strpos($userAgent, 'Opera') !== false) {
                return 'Opera';
            }
        }

        return 'Unknown';
    }

    public function device(?string $userAgent, string $default = 'Mobile'): string
    {
        if ($userAgent) {
            if (strpos($userAgent, 'Mobile') !== false) {
                return 'Mobile';
            }

            if (strpos($userAgent, 'Tablet') !== false) {
                return 'Tablet';
            }

            return 'Desktop';
        }

        return $default;
    }
}
