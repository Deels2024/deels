<?php

declare(strict_types=1);

namespace App\Services\Stories;

use App\Models\Battle;
use App\Models\Challenge;
use App\Models\Story;
use App\Services\UserService;
use App\Services\Contests\ContestNotificationService;
use App\Services\Contests\ContestReportingService;
use Illuminate\Http\Request;

class StoryStoreService
{
    private const VIDEO_MIME_TYPES = [
        'application/octet-stream',
        'video/quicktime',
        'video/mp4',
        'video/mov',
        'video/avi',
        'video/mpeg',
        'video/x-msvideo',
        'video/webm',
    ];

    public function __construct(private StoryLegacyMediaUploader $mediaUploader)
    {
    }

    public function store(Request $request): array
    {
        $validator = validator($request->all(), [
            'amount' => 'sometimes',
        ]);
        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors(),
            ];
        }

        $file = $request->file('mainImg') ?? $request->file('file') ?? $request->file('video');
        if (!$file) {
            return [
                'success' => false,
                'error' => 'Отсутствует контент',
            ];
        }

        $request->files->add(['files' => [$file]]);
        $request->merge(['story' => true]);

        if ($request->files->has('files')) {
            if (!in_array($file->getClientMimeType(), self::VIDEO_MIME_TYPES, true)) {
                return [
                    'success' => false,
                    'error' => 'Используйте видео формата mp4, mov или avi',
                ];
            }

            $storedData = $this->mediaUploader->store($request);
            if (isset($storedData['error'])) {
                return [
                    'success' => false,
                    'error' => $storedData['error'],
                ];
            }
        }

        $mediaId = null;
        if (($storedData['success'] ?? false)) {
            try {
                $mediaId = $storedData['images'][0]->id;
            } catch (\Throwable $e) {
                return [
                    'success' => false,
                    'error' => 'Отсутствует контент',
                ];
            }
        }

        $userId = auth()->id() ?? $request->user_id;
        $challengeId = $request->challenge_id ?? null;
        $battleId = $request->battle_id ?? null;

        $targetError = $request->boolean('online_report')
            ? $this->validateOnlineReport($challengeId, $battleId, (int) $userId)
            : $this->validateTargets($challengeId, $battleId, $userId);
        if ($targetError) {
            return $targetError;
        }

        $paid = $request->paid ?? false;
        if (intval($request->amount) > 0) {
            $paid = true;
        }

        $requestData = json_decode($request->data, true);

        if ($challengeId && !$request->boolean('online_report')) {
            Story::where('challenge_id', $challengeId)
                ->where('user_id', $userId)
                ->where(function ($query): void {
                    $query->where('is_main_story', false)
                        ->orWhereNull('is_main_story');
                })
                ->delete();
        }
        if ($battleId && !$request->boolean('online_report')) {
            Story::withoutGlobalScopes()
                ->where('battle_id', $battleId)
                ->where('user_id', $userId)
                ->where(function ($query): void {
                    $query->where('is_main_story', false)
                        ->orWhereNull('is_main_story');
                })
                ->delete();
        }

        $story = Story::create([
            'user_id' => $userId,
            'description' => $request->description,
            'data' => $requestData,
            'amount' => $request->amount,
            'paid' => $paid,
            'challenge_id' => $challengeId,
            'battle_id' => $battleId,
            'media_id' => $mediaId,
        ]);
        $this->publishAcceptedBattle($story);
        if ($request->boolean('online_report')) {
            $this->attachOnlineReport($challengeId, $battleId, (int) $userId, (int) $story->id);
        }

        if ($request->hasFile('cover')) {
            $file = (new UserService())->uploadCover($request->file('cover'), $story->id);
            $story->cover = $file;
            $story->save();
        }

        return [
            'success' => true,
            'story_id' => $story->id,
        ];
    }

    public function storeWeb(Request $request): array
    {
        $file = $request->file('mainImg') ?? $request->file('file') ?? $request->file('video');
        if (!$file) {
            return [
                'type' => 'back',
                'message' => 'Отсутствует контент',
                'with_input' => true,
            ];
        }

        $validator = validator($request->all(), [
            'amount' => 'sometimes',
        ]);
        if ($validator->fails()) {
            if ($request->ajax()) {
                return [
                    'type' => 'json',
                    'payload' => [
                        'success' => false,
                        'errors' => $validator->errors(),
                    ],
                ];
            }

            return [
                'type' => 'back',
                'message' => 'Ошибка',
            ];
        }

        $request->files->add(['files' => [$file]]);
        $request->merge(['story' => true]);

        if ($request->files->has('files')) {
            if (!in_array($file->getClientMimeType(), self::VIDEO_MIME_TYPES, true)) {
                return [
                    'type' => 'back',
                    'message' => 'Используйте видео формата mp4, mov или avi',
                ];
            }

            $storedData = $this->mediaUploader->store($request);
            if (isset($storedData['error'])) {
                return [
                    'type' => 'back',
                    'message' => $storedData['error'],
                ];
            }
        }

        $mediaId = null;
        if (($storedData['success'] ?? false)) {
            try {
                $mediaId = $storedData['images'][0]->id;
            } catch (\Throwable $e) {
                if ($request->ajax()) {
                    return [
                        'type' => 'json',
                        'payload' => [
                            'success' => false,
                            'error' => 'Отсутствует контент',
                        ],
                    ];
                }

                return [
                    'type' => 'back',
                    'message' => 'Отсутствует контент',
                ];
            }
        }

        $storyUserId = auth()->id() ?? $request->user_id;
        $challengeId = $request->challenge_id ?? null;
        $battleId = $request->battle_id ?? null;

        $targetError = $request->boolean('online_report')
            ? $this->validateOnlineReportWeb($challengeId, $battleId, (int) $storyUserId)
            : $this->validateWebTargets($challengeId, $battleId, $storyUserId);
        if ($targetError) {
            return $targetError;
        }

        $paid = $request->paid ?? false;
        if (intval($request->amount) > 0) {
            $paid = true;
        }

        if ($challengeId && !$request->boolean('online_report')) {
            $this->deleteExistingStory('challenge_id', $challengeId, auth()->id() ?? $request->user_id);
        }
        if ($battleId && !$request->boolean('online_report')) {
            $this->deleteExistingStory('battle_id', $battleId, auth()->id() ?? $request->user_id);
        }

        $story = Story::create([
            'user_id' => $storyUserId,
            'description' => $request->description,
            'amount' => $request->amount,
            'paid' => $paid,
            'challenge_id' => $challengeId,
            'battle_id' => $battleId,
            'media_id' => $mediaId,
        ]);
        $this->publishAcceptedBattle($story);
        if ($request->boolean('online_report')) {
            $this->attachOnlineReport($challengeId, $battleId, (int) $storyUserId, (int) $story->id);
        }

        if ($request->hasFile('cover')) {
            try {
                $file = (new UserService())->uploadCover($request->file('cover'), $story->id);
                $story->cover = $file;
                $story->save();
            } catch (\Throwable $e) {
            }
        }

        if ($request->ajax()) {
            return [
                'type' => 'json',
                'payload' => [
                    'success' => true,
                    'story_id' => $story->id,
                ],
            ];
        }

        return [
            'type' => 'redirect',
            'route' => 'user_stories',
            'message' => 'Сторис загружена! Хранение сторис платное.',
        ];
    }

    private function validateTargets($challengeId, $battleId, $userId): ?array
    {
        if ($challengeId) {
            $challenge = Challenge::find($challengeId);
            if (!$challenge) {
                return [
                    'success' => false,
                    'error' => 'Челлендж не найден',
                ];
            }
            if ($challenge->frozen) {
                return [
                    'success' => false,
                    'error' => 'Челлендж заморожен. Вы не можете участвовать в этом челлендже.',
                ];
            }
            if (!$challenge->active || $challenge->finished || $challenge->declined) {
                return [
                    'success' => false,
                    'error' => 'Челлендж не активен или завершен',
                ];
            }
            if ($userId == $challenge->user_id) {
                return [
                    'success' => false,
                    'error' => 'Вы не можете участвовать в своем челлендже',
                ];
            }
        }

        if ($battleId) {
            $battle = Battle::find($battleId);
            if (!$battle) {
                return [
                    'success' => false,
                    'error' => 'Челлендж не найден',
                ];
            }
            if ($battle->frozen) {
                return [
                    'success' => false,
                    'error' => 'Батл заморожен. Вы не можете участвовать в этом батле.',
                ];
            }
            if (!$battle->active || $battle->finished || $battle->declined) {
                return [
                    'success' => false,
                    'error' => 'Батл не активен или завершен',
                ];
            }
            if ($userId == $battle->user_id) {
                return [
                    'success' => false,
                    'error' => 'Вы не можете участвовать в своем батле',
                ];
            }
        }

        return null;
    }

    private function validateWebTargets($challengeId, $battleId, $storyUserId): ?array
    {
        if ($challengeId) {
            $challenge = Challenge::find($challengeId);
            if (!$challenge) {
                return ['type' => 'back', 'message' => 'Челлендж не найден'];
            }
            if ($challenge->frozen) {
                return ['type' => 'back', 'message' => 'Челлендж заморожен. Вы не можете участвовать в этом челлендже.'];
            }
            if (!$challenge->active || $challenge->finished || $challenge->declined) {
                return ['type' => 'back', 'message' => 'Челлендж не активен или завершен'];
            }
            if ($storyUserId == $challenge->user_id) {
                return ['type' => 'back', 'message' => 'Вы не можете участвовать в своем челлендже'];
            }
        }

        if ($battleId) {
            $battle = Battle::find($battleId);
            if (!$battle) {
                return ['type' => 'back', 'message' => 'Батл не найден'];
            }
            if ($battle->frozen) {
                return ['type' => 'back', 'message' => 'Батл заморожен. Вы не можете участвовать в этом батле.'];
            }
            if (!$battle->active || $battle->finished || $battle->declined) {
                return ['type' => 'back', 'message' => 'Батл не активен или завершен'];
            }
            if ($storyUserId == $battle->user_id) {
                return ['type' => 'back', 'message' => 'Вы не можете участвовать в своем батле'];
            }
        }

        return null;
    }

    private function deleteExistingStory(string $targetColumn, $targetId, $userId): void
    {
        $story = Story::where($targetColumn, $targetId)
            ->where('user_id', $userId)
            ->where(function ($query): void {
                $query->where('is_main_story', false)
                    ->orWhereNull('is_main_story');
            })
            ->first();

        if ($story) {
            $story->delete();
        }
    }

    private function validateOnlineReport($challengeId, $battleId, int $userId): ?array
    {
        try {
            $this->onlineReportState($challengeId, $battleId, $userId);
            return null;
        } catch (\Illuminate\Validation\ValidationException $exception) {
            return ['success' => false, 'error' => $exception->validator->errors()->first()];
        }
    }

    private function validateOnlineReportWeb($challengeId, $battleId, int $userId): ?array
    {
        $error = $this->validateOnlineReport($challengeId, $battleId, $userId);

        return $error ? ['type' => 'json', 'payload' => $error] : null;
    }

    private function onlineReportState($challengeId, $battleId, int $userId): array
    {
        $type = $battleId ? 'battle' : 'challenge';
        $contest = $battleId ? Battle::find($battleId) : Challenge::find($challengeId);
        if (!$contest) {
            throw \Illuminate\Validation\ValidationException::withMessages(['report' => 'Событие не найдено']);
        }
        $state = app(ContestReportingService::class)->state($contest, $type, $userId);
        if (!$state['visible'] || $state['checkin'] !== 'story' || !$state['story_allowed']) {
            throw \Illuminate\Validation\ValidationException::withMessages(['report' => 'Онлайн-сторис сейчас недоступна']);
        }

        return [$contest, $type];
    }

    private function attachOnlineReport($challengeId, $battleId, int $userId, int $storyId): void
    {
        [$contest, $type] = $this->onlineReportState($challengeId, $battleId, $userId);
        app(ContestReportingService::class)->attachStory($contest, $type, $userId, $storyId);
    }

    private function publishAcceptedBattle(Story $story): void
    {
        if (!$story->battle_id) {
            return;
        }

        $battle = Battle::find($story->battle_id);
        if ($battle && (int) $battle->called_user_id === (int) $story->user_id) {
            app(ContestNotificationService::class)->battleAccepted($battle);
        }
    }
}
