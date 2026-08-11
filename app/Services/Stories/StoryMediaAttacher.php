<?php

declare(strict_types=1);

namespace App\Services\Stories;

use App\Services\MediaUploadService;
use Illuminate\Http\Request;

class StoryMediaAttacher
{
    public function __construct(
        private StoryUploadFileValidator $fileValidator,
        private MediaUploadService $mediaUploadService
    ) {
    }

    public function attach(Request $request, $file): array
    {
        $validationError = $this->fileValidator->validate($file);
        if ($validationError) {
            return [
                'media_id' => null,
                'error' => $validationError,
            ];
        }

        $request->files->add(['files' => [$file]]);
        $request->merge(['story' => true]);

        $storedData = $this->mediaUploadService->store($request);

        if (isset($storedData['error'])) {
            return [
                'media_id' => null,
                'error' => $storedData['error'],
            ];
        }

        $mediaId = $this->getMediaId($storedData);
        if (!$mediaId) {
            return [
                'media_id' => null,
                'error' => 'Отсутствует контент',
            ];
        }

        return [
            'media_id' => $mediaId,
            'error' => null,
        ];
    }

    private function getMediaId($storedData)
    {
        try {
            return $storedData['images'][0]->id;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
