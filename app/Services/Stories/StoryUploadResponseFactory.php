<?php

declare(strict_types=1);

namespace App\Services\Stories;

class StoryUploadResponseFactory
{
    public function validationError($validator, bool $isApi)
    {
        if ($isApi) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ]);
        }

        return back()->with('error', 'Ошибка');
    }

    public function error(string $message, bool $isApi)
    {
        if ($isApi || request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => false,
                'error' => $message
            ]);
        }

        return back()->with('error', $message);
    }

    public function requestAwareError(string $message, bool $withInput = false)
    {
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => false,
                'error' => $message
            ]);
        }

        $response = back()->with('error', $message);

        return $withInput ? $response->withInput() : $response;
    }

    public function success(
        int $storyId,
        bool $isApi,
        $challengeId = null,
        $battleId = null,
        bool $isUseful = false
    )
    {
        if ($isApi || request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'story_id' => $storyId
            ]);
        }

        if ($isUseful && $challengeId) {
            return redirect()->route('challenge_page', ['id' => $challengeId])->with('success', 'Полезное добавлено!');
        }

        if ($isUseful && $battleId) {
            return redirect()->route('battle_page', ['id' => $battleId])->with('success', 'Полезное добавлено!');
        }

        return redirect()->route('user_stories')->with('success', 'Сторис загружена! Хранение сторис платное.');
    }
}
