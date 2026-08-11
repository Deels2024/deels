<?php

declare(strict_types=1);

namespace App\Services\Stories;

use App\Http\Controllers\MediaController;
use Illuminate\Http\Request;

class StoryLegacyMediaUploader
{
    public function store(Request $request): array
    {
        return (new MediaController())->store($request);
    }
}
