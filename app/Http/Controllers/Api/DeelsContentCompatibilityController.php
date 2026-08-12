<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeelsContentCompatibilityController extends Controller
{
    public function createChallenge(Request $request): JsonResponse
    {
        $this->prepareChallengeRequest($request);

        return $this->storeChallenge($request);
    }

    public function updateChallenge(Request $request, $id): JsonResponse
    {
        $this->prepareChallengeRequest($request, (int) $id);

        return $this->storeChallenge($request);
    }

    public function joinChallenge(Request $request, $id): JsonResponse
    {
        $validator = validator($request->all(), [
            'media' => 'required|file',
            'caption' => 'nullable|string|max:5000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Проверьте видеоответ',
                'errors' => $validator->errors(),
            ], 422);
        }

        if ($request->hasFile('media')) {
            $request->files->set('file', $request->file('media'));
        }
        $request->merge([
            'challenge_id' => (int) $id,
            'description' => $request->input('caption', ''),
            'amount' => 0,
        ]);

        return $this->storeStoryAndReturn($request);
    }

    public function createStory(Request $request): JsonResponse
    {
        $validator = validator($request->all(), [
            'media' => 'required|file',
            'description' => 'nullable|string|max:5000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Проверьте сторис',
                'errors' => $validator->errors(),
            ], 422);
        }

        if ($request->hasFile('media')) {
            $request->files->set('file', $request->file('media'));
        }
        $request->merge(['amount' => 0]);

        return $this->storeStoryAndReturn($request);
    }

    private function prepareChallengeRequest(Request $request, ?int $id = null): void
    {
        $validator = validator($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'prize' => 'nullable|numeric|min:0',
            'ends_at' => 'nullable|date',
            'media' => $id ? 'nullable|file' : 'required|file',
        ]);

        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }

        if ($request->hasFile('media')) {
            $request->files->set('mainImg', $request->file('media'));
        }

        $days = 7;
        if ($request->filled('ends_at')) {
            $end = Carbon::parse((string) $request->input('ends_at'));
            $days = max(1, Carbon::now()->startOfDay()->diffInDays($end->startOfDay(), false));
        }

        $request->merge([
            'challenge_id' => $id,
            'amount' => 0,
            'reward_amount' => $request->input('prize'),
            'criteria' => ['by_likes'],
            'min_participants' => 0,
            'days' => $days,
            'participants_visual' => '0',
            'visibility' => 'all',
            'rhythm_visual' => 'daily',
            'checkin_visual' => 'story',
            'winner_selection' => 'likes',
            'cost' => 0,
        ]);
    }

    private function storeChallenge(Request $request): JsonResponse
    {
        $legacy = app(ChallengeController::class)->store_web($request);
        if (!$legacy instanceof JsonResponse) {
            return response()->json([
                'success' => false,
                'message' => 'Не удалось сохранить челлендж',
            ], 422);
        }

        $payload = $legacy->getData(true);
        if (empty($payload['success'])) {
            return response()->json([
                'success' => false,
                'message' => $payload['error'] ?? 'Проверьте параметры челленджа',
                'error' => $payload['error'] ?? null,
                'errors' => $payload['errors'] ?? [],
            ], 422);
        }

        $id = (int) ($payload['challenge_id'] ?? $request->input('challenge_id'));
        $data = app(ChallengeController::class)->get($request, $id, true, false);
        if ($data instanceof JsonResponse) {
            return $data;
        }

        $data['id'] = $data['challenge_id'] ?? $id;
        $data['prize_amount'] = $data['reward_amount'] ?? 0;
        $data['media_url'] = $data['video_preview'] ?? $data['path'] ?? null;

        return response()->json(['success' => true, 'data' => $data]);
    }

    private function storeStoryAndReturn(Request $request): JsonResponse
    {
        $legacy = app(StoryController::class)->store($request);
        $payload = $legacy->getData(true);

        if (empty($payload['success'])) {
            return response()->json([
                'success' => false,
                'message' => $payload['error'] ?? 'Не удалось сохранить сторис',
                'error' => $payload['error'] ?? null,
                'errors' => $payload['errors'] ?? [],
            ], 422);
        }

        $id = (int) ($payload['story_id'] ?? 0);
        $data = app(StoryController::class)->get($request, $id, true, false);
        if ($data instanceof JsonResponse) {
            return $data;
        }

        $data['id'] = $data['story_id'] ?? $id;
        $data['media_url'] = $data['video_preview'] ?? $data['path'] ?? null;

        return response()->json(['success' => true, 'data' => $data]);
    }
}
