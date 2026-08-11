<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserRequestContextService
{
    public function __construct(private UserAgentParser $userAgentParser)
    {
    }

    public function build(Request $request, bool $isApp = false, string $defaultDevice = 'Mobile'): array
    {
        try {
            $data = [
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'browser' => $this->userAgentParser->browser($request->userAgent()),
                'device' => $this->userAgentParser->device($request->userAgent(), $defaultDevice),
            ];

            if ($isApp) {
                $data['is_app'] = true;
            }

            $data['headers'] = $request->header();

            return $data;
        } catch (\Throwable $e) {
            Log::info($e->getMessage());

            return [];
        }
    }
}
