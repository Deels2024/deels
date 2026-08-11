<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Battle;
use App\Models\Challenge;
use App\Models\Media;
use App\Services\Contests\ContestReportingService;
use App\Services\Stories\StoryAdValidator;
use App\Services\Stories\StoryCreator;
use App\Services\Stories\StoryMediaAttacher;
use App\Services\Stories\StoryParticipationPaymentService;
use App\Services\Stories\StoryReplacementService;
use App\Services\Stories\StoryUploadResponseFactory;
use App\Services\Stories\StoryTargetValidator;
use App\Services\Stories\StoryVideoProcessor;
use Illuminate\Http\Request;

class StoryUploadController extends Controller
{
    public function __construct(
        private StoryUploadResponseFactory $responses,
        private StoryParticipationPaymentService $paymentService,
        private StoryTargetValidator $targetValidator,
        private StoryReplacementService $replacementService,
        private StoryCreator $storyCreator,
        private StoryVideoProcessor $videoProcessor,
        private StoryMediaAttacher $mediaAttacher,
        private StoryAdValidator $adValidator
    ) {
    }

    public function store(Request $request)
    {
        return $this->processStory($request, true);
    }

    public function store_web(Request $request)
    {
        return $this->processStory($request, false);
    }

    private function processStory(Request $request, $isApi)
    {
        if (!$request->filled('amount') || !is_numeric($request->input('amount'))) {
            $request->merge(['amount' => 0]);
        }

        $adValidation = $this->adValidator->validate($request);
        if (!$adValidation->valid) {
            return $this->responses->requestAwareError($adValidation->error, true);
        }
        $ads_data = $adValidation->adsData;

        $rules = ['amount' => ['required', 'numeric', 'min:0']];
        $validator = validator($request->all(), $rules);

        $user_id = auth()->id() ?? $request->user_id;
        $challenge_id = $request->challenge_id;
        $battle_id = $request->battle_id;
        $campaign_id = $request->campaign_id;
        $isUseful = $request->boolean('is_useful');
        $isOnlineReport = $request->boolean('online_report');

        if ($validator->fails()) {
            return $this->responses->validationError($validator, (bool) $isApi);
        }

        $media_id = $request->input('media_id');

        $file = $request->file('mainImg') ?? $request->file('file') ?? $request->file('video');

        if (!$file && !$media_id) {
            return $this->responses->error('Отсутствует контент', (bool) $isApi);
        }

        if ($isUseful && (bool) $challenge_id === (bool) $battle_id) {
            return $this->responses->error('Для полезной сторис должен быть указан один челлендж или батл', (bool) $isApi);
        }

        if ($isUseful && $challenge_id && !$this->targetValidator->validateUsefulChallenge($challenge_id, $user_id)) {
            return $this->responses->error('Добавлять полезное может только автор челленджа', (bool) $isApi);
        }

        if ($isUseful && $battle_id && !$this->targetValidator->validateUsefulBattle($battle_id, $user_id)) {
            return $this->responses->error('Добавлять полезное может только автор батла', (bool) $isApi);
        }

        if ($isOnlineReport && (bool) $challenge_id === (bool) $battle_id) {
            return $this->responses->requestAwareError('Для отчётной сторис должно быть указано одно событие');
        }

        if ($isOnlineReport) {
            $contestType = $battle_id ? 'battle' : 'challenge';
            $contest = $battle_id ? Battle::find($battle_id) : Challenge::find($challenge_id);
            $reportingState = $contest
                ? app(ContestReportingService::class)->state($contest, $contestType, (int) $user_id)
                : ['visible' => false, 'checkin' => null, 'story_allowed' => false];

            if (!$reportingState['visible']
                || $reportingState['checkin'] !== 'story'
                || !$reportingState['story_allowed']) {
                return $this->responses->requestAwareError('Онлайн-сторис сейчас недоступна');
            }
        }

        if (!$isUseful && !$isOnlineReport && $challenge_id && !$this->targetValidator->validateChallenge($challenge_id, $user_id)) {
            return $this->responses->error('Челлендж не найден или не активен', (bool) $isApi);
        }

        if (!$isUseful && !$isOnlineReport && $battle_id && !$this->targetValidator->validateBattle($battle_id, $user_id)) {
            return $this->responses->error('Батл не найден или не активен', (bool) $isApi);
        }

        if ($campaign_id && !$this->targetValidator->validateCampaign($campaign_id, $user_id)) {
            return $this->responses->error('Копилка не найдена или недоступна', (bool) $isApi);
        }

        $hax_existing_story = !$isUseful && !$isOnlineReport && $this->replacementService->hasChallengeStory($challenge_id, $user_id);
        $hax_existing_battle_story = !$isUseful && !$isOnlineReport && $this->replacementService->hasBattleStory($battle_id, $user_id);


        $paymentError = $this->paymentService->payForChallengeIfNeeded(
            $isUseful || $isOnlineReport ? null : $challenge_id,
            $user_id,
            $hax_existing_story
        );
        if ($paymentError) {
            return $this->responses->requestAwareError($paymentError, true);
        }

        $paymentError = $this->paymentService->payForBattleIfNeeded(
            $isUseful || $isOnlineReport ? null : $battle_id,
            $user_id,
            $hax_existing_battle_story
        );
        if ($paymentError) {
            return $this->responses->requestAwareError($paymentError, true);
        }


        if (!$media_id) {
            $attachedMedia = $this->mediaAttacher->attach($request, $file);
            if ($attachedMedia['error']) {
                return $this->responses->error($attachedMedia['error'], (bool) $isApi);
            }
            $media_id = $attachedMedia['media_id'];
        }

        $paid = $this->determinePaidStatus($request);

        if (!$isUseful && !$isOnlineReport) {
            $this->replacementService->deleteChallengeStory($challenge_id, $user_id);
            $this->replacementService->deleteBattleStory($battle_id, $user_id);
        }

        $story = $this->storyCreator->create($request, $user_id, $media_id, $paid, $ads_data ?? []);

        if ($isOnlineReport) {
            try {
                app(ContestReportingService::class)->attachStory(
                    $contest,
                    $contestType,
                    (int) $user_id,
                    (int) $story->id
                );
            } catch (\Illuminate\Validation\ValidationException $exception) {
                $story->delete();
                return $this->responses->requestAwareError($exception->validator->errors()->first());
            }
        }

        if ($request->hasFile('cover')) {
            $this->videoProcessor->uploadCover($request->file('cover'), $story);
        }

        if ($story->type == 'video') {
            $this->videoProcessor->process($story, $paid);
        }

        Campaign::healthUp($challenge_id ? 5 : 3, (int) $user_id);

        return $this->responses->success(
            (int) $story->id,
            (bool) $isApi,
            $challenge_id,
            $battle_id,
            $isUseful
        );
    }

    public function videoUpload(Request $request)
    {
        $isApi = true;
        $file = $request->file('mainImg') ?? $request->file('file');

        if (!$file) {
            return $this->responses->error('Отсутствует контент', (bool) $isApi);
        }


        $attachedMedia = $this->mediaAttacher->attach($request, $file);
        if ($attachedMedia['error']) {
            return $this->responses->error($attachedMedia['error'], (bool) $isApi);
        }

        $media = Media::find($attachedMedia['media_id']);

        return response()->json([
            'success' => true,
            'type' => $media->type,
            'file_size' => $media->file_size,
            'path' => $media->path_url,
            'webp_path' => $media->webp_path_url
        ]);

    }

    private function determinePaidStatus(Request $request): bool
    {
        return (int) $request->input('amount', 0) > 0 || $request->boolean('paid');
    }

}
