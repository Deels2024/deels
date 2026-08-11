<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Http\Controllers\MediaController;
use App\Models\Abuse;
use App\Models\Challenge;
use App\Models\Story;
use App\Models\User;
use App\Rules\MaxWords;
use App\Services\ApiAccountInfoService;
use App\Services\ApiStoryFeedFormatter;
use App\Services\Contests\ChallengeWinnerSelectionService;
use App\Services\Contests\ContestDetailFormatter;
use App\Services\Contests\ContestListService;
use App\Services\Contests\ContestNotificationService;
use App\Services\Contests\ContestVisibilityService;
use Carbon\Carbon;
use FFMpeg\FFMpeg;
use FFMpeg\Coordinate\TimeCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;
use Pawlox\VideoThumbnail\VideoThumbnail;

class ChallengeController extends Controller
{

    public function get_challenges(Request $request)
    {
        $listService = app(ContestListService::class);
        $media = $listService->contests($request);

        if (request()->wantsJson()) {
            return response()->json($listService->formatPaginator($media));
        }

        return view('challenges_index', ['challenges' => $media]);
    }

    public function get_popular_answers(Request $request)
    {
        $user_id = $request->input('user_id');
        $media_query = Story::with('comments', 'likes')
            ->whereNotNull('challenge_id')
            ->notUseful()
            ->where('active', true)
            ->where('declined', false)
            ->notMainStory()
            ->withCount('comments', 'likes', 'views')
            ->orderBy('views_count', 'desc')
            ->orderBy('likes_count', 'desc')
            ->orderBy('comments_count', 'desc')
            ->orderBy('created_at', 'DESC');
        app(ContestVisibilityService::class)->applyToStories($media_query, Auth::user() ?? auth()->user());

        $media = $media_query->paginate(8)->appends(request()->query());

        if (request()->wantsJson()) {
            $data = app(ApiStoryFeedFormatter::class)->format($media, $user_id);

            return response()->json([
                'success' => true,
                'data' => $data,
                'current_page' => $media->currentPage(),
                'total_pages' => $media->lastPage(),
            ]);
        }
    }

    public function show($id)
    {
        $challenge = Challenge::find($id);
        if (!$challenge) {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Челлендж не найден'
                ]);
            } else {
                abort(404);
            }
        }

        if (!app(ContestVisibilityService::class)->canView($challenge, Auth::user() ?? auth()->user())) {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Просмотр недоступен',
                ], 403);
            }

            return response()->view('contests.private', ['contest' => $challenge]);
        }

        if (!$challenge->active || $challenge->declined) {
            $viewer = Auth::user();
            $canViewInactive = $viewer
                && ($viewer->is_admin() || (int) $challenge->user_id === (int) $viewer->id);

            if (!$canViewInactive) {
                if (request()->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Челлендж не найден'
                    ]);
                } else {
                    abort(404);
                }
            }
        }

        $title = 'Челлендж ' . $challenge->title;
        $stories = $challenge->stories()->active()->orderByDesc('is_main_story')->latest()->paginate(500);
        $stories_count = $challenge->stories()->active()->count();
        return view('challenges.page_front', compact('challenge', 'title', 'stories', 'stories_count'));
    }

    public function store_web(Request $request)
    {
        $challange_coin = false;
        $rules = [
            'amount' => $challange_coin ? 'required|numeric|min:100|max:10000' : 'nullable|numeric|min:0|max:10000',
            'title' => 'required',
            'description' => ['required', 'string', 'max:5000', new MaxWords(650)],
            'date_from' => 'sometimes|date',
            'criteria' => 'array|min:1|required',
            'min_participants' => 'required|integer|min:0|max:100',
            'days' => 'numeric|min:1|required',
            'date_from_visual' => 'nullable|date_format:d.m.y H:i',
            'date_to_visual' => 'nullable|date_format:d.m.y H:i',
            'participants_visual' => 'nullable|in:0,1,2,limit',
            'min_participants_limit' => 'exclude_unless:participants_visual,limit|required|integer|min:2|max:100',
            'reward_amount' => 'nullable|integer|min:1',
            'winner_selection' => 'nullable|in:likes,creator',
            'visibility' => 'nullable|in:all,friends,participants',
            'invite_user_ids' => 'nullable|array',
            'invite_user_ids.*' => 'integer|exists:users,id',
        ];

        $request->merge(['date_from' => Carbon::now()]);
        if (!$challange_coin) {
            $request->merge(['amount' => 0]);
        }
        if ($request->has('reward_amount')
            && is_numeric($request->input('reward_amount'))
            && (float) $request->input('reward_amount') === 0.0) {
            $request->merge(['reward_amount' => null]);
        }

        if ($request->input('challenge_id')) {
            $challenge = Challenge::find($request->input('challenge_id'));
            $error_message = null;
            if (!$challenge || ($challenge && $challenge->user_id != Auth::user()->id && !Auth::user()->is_admin())) {
                $error_message = 'Вы не можете редактировать этот челлендж';
            }
            if ($challenge && $challenge->finished) {
                $error_message = 'Челлендж уже завершен';
                if(Auth::user() && Auth::user()->is_admin()) {
                    $error_message = null;
                }
            }
            if ($challenge && $challenge->declined) {
                $error_message = 'Челлендж отклонен';
            }

            if ($error_message) {
                if ($request->ajax() || request()->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'errors' => $error_message
                    ]);
                } else {
                    return back()->with('error', $error_message)->withInput();
                }
            }
        }

        if ($request->has('start_date') && $request->get('start_date') != $request->get('end_date')) {
            $rules['date_to'] = 'required|date_format:Y-m-d|after:date_from';
        } else {
            $rules['date_to'] = 'required|date_format:Y-m-d|after:' . Carbon::now()->format('d.m.Y');
        }
        if($request->get('days') && $request->get('days') > 0) {
            unset($rules['date_to']);
        }
        $image = '';
        $images = [];
        $file = $request->file('mainImg') ?? $request->file('file') ?? [];
        $validator = validator($request->all(), $rules);
        $requiresCover = !isset($challenge) || !$challenge->media_id;

        if ($validator->fails()) {
            if ($request->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ]);
            } else {
                return back()->with('error', 'Заполните поля')->withErrors($validator)->withInput();
            }

        }
        if ($requiresCover && !$file) {
            if ($request->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => ['mainImg' => ['Добавьте обложку']]
                ]);
            } else {
                return back()->with('error', 'Добавьте обложку')->withInput();
            }
        }
        $visualDateErrors = $this->validateVisualDates($request, $challenge ?? null);
        if ($visualDateErrors->isNotEmpty()) {
            if ($request->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $visualDateErrors
                ]);
            } else {
                return back()->with('error', 'Проверьте срок')->withErrors($visualDateErrors)->withInput();
            }
        }
        $storedData = null;
        if ($file) {
            $request->files->add(['files' => [$file]]);
            $request->merge(['story' => true, 'max_video_seconds' => 5]);
            $storedData = (new MediaController())->store($request);
            if (isset($storedData['error'])) {
                if (request()->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'errors' => $storedData['error']
                    ]);
                } else {
                    return back()->with('error', $storedData['error'])->withInput();
                }

            }
            if (isset($storedData['images'])) {
                $images = array_column($storedData['images'], 'id');
            }
            $image = array_shift($images);
        }
        $media_id = null;
        if ($file && !empty($storedData['success'])) {
            try {
                $media_id = $storedData['images'][0]->id;
            } catch (\Throwable $e) {

                if ($request->ajax() || request()->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Отсутствует контент'
                    ]);
                } else {
                    return back()->with('error', 'Отсутствует контент')->withInput();
                }

            }
        }

        $days = $request->days;
        $min_participants = max(0, min(100, (int) $request->min_participants));
        $participants_count = null;
        switch ((string) $request->input('participants_visual')) {
            case 'limit':
                $participants_count = $request->input('min_participants_limit') !== null
                    ? max(2, min(100, (int) $request->input('min_participants_limit')))
                    : null;
                $min_participants = $participants_count ?? 0;
                break;
            case '0':
            case '1':
            case '2':
                $participants_count = (int) $request->input('participants_visual');
                $min_participants = $participants_count;
                break;
        }
        $cost = $request->cost;
        $date_from = $this->parseVisualDate($request->input('date_from_visual'), Carbon::now());
        $date_to = $this->parseVisualDate($request->input('date_to_visual'), $date_from->copy()->addDays(7));
        $reward_amount = $request->filled('reward_amount') ? (int) $request->input('reward_amount') : null;
        $winner_selection = $request->input('winner_selection') ?: 'likes';
        $invite_user_ids = collect($request->input('invite_user_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0 && $id !== (int) (auth()->id() ?? $request->user_id))
            ->unique()
            ->values()
            ->toArray();
        if (isset($challenge)) {
            $invite_user_ids = $this->mergeExistingInviteUserIds($challenge, $invite_user_ids);
            $participantsErrors = $this->validateParticipantsEdit($challenge, $participants_count);
            $startedFieldErrors = $this->validateStartedFieldChanges($challenge, $request);
            $participantsErrors->merge($startedFieldErrors);
            if ($participantsErrors->isNotEmpty()) {
                if ($request->ajax() || request()->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'errors' => $participantsErrors
                    ]);
                } else {
                    return back()->with('error', 'Проверьте число участников')->withErrors($participantsErrors)->withInput();
                }
            }
        }

        if ($participants_count === 1) {
            $reward_amount = null;
            $winner_selection = null;
        } elseif ($request->input('checkin_visual') !== 'story' && $winner_selection === 'likes') {
            $winner_selection = 'creator';
        }

        $user = User::find(auth()->id() ?? $request->user_id);
        if ($reward_amount !== null && $user && $reward_amount > (int) $user->wallet_balance) {
            if ($request->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => ['reward_amount' => ['Ваш счет дилсов: ' . (int) $user->wallet_balance]]
                ]);
            } else {
                return back()->with('error', 'Ваш счет дилсов: ' . (int) $user->wallet_balance)->withInput();
            }
        }

        $finish_date = $date_to->copy();
        $data = [
            'user_id' => $challenge->user_id ?? auth()->id() ?? $request->user_id,
            'title' => $request->title,
            'description' => $request->description,
            'amount' => $request->amount,
            'reward_amount' => $reward_amount,
            'start' => null,
            'days' => $days,
            'min_participants' => $min_participants,
            'participants_count' => $participants_count,
            'visibility' => $request->input('visibility'),
            'rhythm' => $request->input('rhythm_visual'),
            'checkin' => $request->input('checkin_visual'),
            'winner_selection' => $winner_selection,
            'invite_user_ids' => $invite_user_ids,
            'date_from' => $date_from,
            'date_to' => $date_to,
            'cost' => $cost ?? 0,
            'finish' => $finish_date,
            'media_id' => $media_id ?? $challenge->media_id ?? null,
            'by_views' => in_array('by_views', $request->criteria),
            'by_likes' => in_array('by_likes', $request->criteria),
            'by_comments' => in_array('by_comments', $request->criteria),
        ];
        $data = $this->filterChallengeColumns($data);
        if (isset($challenge)) {
            $data['active'] = 0;
            $challenge->update($data);
            $create = $challenge;
        } else {
            if ($challange_coin && intval($request->amount) > 0) {
                $payments_wallet = $user->getWallet('payments');
                try {
                    $payments_wallet->withdraw(intval($request->amount), ['create' => 'challenge', 'description' => 'Оплата за создание челленджа']);
                } catch (\Throwable $e) {
                    if ($request->ajax() || request()->wantsJson()) {
                        return response()->json([
                            'success' => false,
                            'error' => 'Недостаточно дилсов. Пополните баланс!'
                        ]);
                    } else {
                        return back()->with('error', 'Недостаточно дилсов. Пополните баланс!')->withInput();
                    }
                }
            }
            $create = Challenge::create($data);
        }

        if ($request->hasFile('intro_story')) {
            $oldMainStory = $create->getMainStory()->first();
            $coverUsesOldMainStory = $oldMainStory
                && (int) $create->media_id === (int) $oldMainStory->media_id;
            $shouldUseMainStoryAsCover = !$file
                && (!$create->media_id || $coverUsesOldMainStory);
            $mainStory = $this->replaceMainStory($request, $create);
            if ($shouldUseMainStoryAsCover && $mainStory && $mainStory->media_id) {
                $create->media_id = $mainStory->media_id;
                $create->saveQuietly();
            }
        }


        if ($file) {
            $this->processCoverMedia($create, 'challenge');
        }

        if ($request->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'challenge_id' => $create->id,
                'clear_challenge_create_draft' => !isset($challenge),
            ]);
        } else {
            $success_message = isset($challenge) ? 'Челлендж изменен!' : 'Челлендж создан!';
            if (isset($challenge)) {
                return redirect()
                    ->route('dashboard_challenge_page', $create->id)
                    ->with('success', $success_message);
            }

            return redirect()
                ->route('challenge_page', $create->id)
                ->with('success', $success_message)
                ->with('clear_challenge_create_draft', true);
        }

    }

    private function replaceMainStory(Request $request, Challenge $challenge): ?Story
    {
        $oldStory = $challenge->getMainStory()->first();
        if ($oldStory) {
            if ($oldStory->media) {
                $oldStory->media->delete();
            }
            $oldStory->delete();
        }

        $file = $request->file('intro_story');
        if (!$file) {
            return null;
        }

        $storyRequest = Request::create('', 'POST', [
            'story' => true,
            'max_video_seconds' => 5,
            'user_id' => $challenge->user_id,
        ]);
        $storyRequest->setUserResolver(fn () => $request->user());
        $storyRequest->files->set('files', [$file]);

        $storedData = (new MediaController())->store($storyRequest);
        if (empty($storedData['success']) || empty($storedData['images'][0])) {
            return null;
        }

        $story = Story::create([
            'user_id' => $challenge->user_id,
            'description' => $challenge->description,
            'amount' => 0,
            'paid' => false,
            'active' => true,
            'declined' => false,
            'challenge_id' => $challenge->id,
            'media_id' => $storedData['images'][0]->id,
            'is_main_story' => true,
        ]);

        if ($story->type === 'video') {
            $story->thumbnail;
            $story->video_preview;
        }

        return $story;
    }

    private function generateVideoPreview($media, string $videoPath): void
    {
        if (!$media || $media->video_preview || !file_exists($videoPath)) {
            return;
        }

        try {
            $ffmpeg = FFMpeg::create([
                'ffmpeg.binaries' => env('FFMPEG'),
                'ffprobe.binaries' => env('FFPROBE'),
            ]);
            $mediaPath = $media->folder ? rtrim($media->folder, '/') . '/' : 'uploads/challenges/';
            $previewPath = $mediaPath . 'preview_' . $media->slug_ext;
            $previewPath = ltrim(str_replace('//', '/', $previewPath), '/');
            $previewFullPath = public_path($previewPath);

            $format = new \FFMpeg\Format\Video\X264('aac', 'libx264');
            $format->setKiloBitrate(20000);
            $format->setAdditionalParameters([
                '-preset', 'slow',
                '-crf', '22',
                '-pix_fmt', 'yuv420p',
                '-movflags', '+faststart',
                '-vf', 'scale=trunc(iw/2)*2:trunc(ih/2)*2',
            ]);

            $ffmpeg->open($videoPath)
                ->clip(TimeCode::fromSeconds(0), TimeCode::fromSeconds(3))
                ->save($format, $previewFullPath);

            $media->video_preview = $previewPath;
            $media->save();
        } catch (\Throwable $e) {
            \Log::error('Ошибка генерации видео превью челленджа: ' . $e->getMessage());
        }
    }

    private function processCoverMedia(Challenge $challenge, string $prefix): void
    {
        $media = $challenge->media;
        if (!$media) {
            return;
        }

        $mediaPath = $media->folder ? rtrim($media->folder, '/') . '/' : 'uploads/challenges/';
        $filePath = public_path($mediaPath . $media->slug_ext);
        if (!file_exists($filePath)) {
            return;
        }

        if ($challenge->type === 'video') {
            $filePath = $this->normalizeCoverVideo($media, $filePath, $mediaPath);
            $this->generateCoverThumbnailFromVideo($media, $filePath, $prefix, $challenge->id);
            $this->generateVideoPreview($media, $filePath);
            return;
        }

        $this->generateCoverThumbnailFromImage($media, $filePath, $prefix, $challenge->id);
    }

    private function normalizeCoverVideo($media, string $sourcePath, string $mediaPath): string
    {
        $normalizedSlug = preg_match('/^c_/', (string) $media->slug) ? $media->slug : 'c_' . $media->slug;
        $normalizedSlugExt = preg_match('/^c_/', (string) $media->slug_ext) ? $media->slug_ext : 'c_' . $media->slug_ext;
        $normalizedPath = public_path($mediaPath . $normalizedSlugExt);

        if ($normalizedPath === $sourcePath) {
            return $sourcePath;
        }

        try {
            $ffmpeg = FFMpeg::create([
                'ffmpeg.binaries' => env('FFMPEG'),
                'ffprobe.binaries' => env('FFPROBE'),
            ]);
            $format = new \FFMpeg\Format\Video\X264('aac', 'libx264');
            $format->setKiloBitrate(20000);
            $format->setAdditionalParameters([
                '-preset', 'slow',
                '-crf', '18',
                '-pix_fmt', 'yuv420p',
                '-movflags', '+faststart',
                '-vf', 'scale=720:1280:force_original_aspect_ratio=decrease,pad=720:1280:(ow-iw)/2:(oh-ih)/2',
            ]);

            $ffmpeg->open($sourcePath)->save($format, $normalizedPath);
            File::delete($sourcePath);
            $media->slug = $normalizedSlug;
            $media->slug_ext = $normalizedSlugExt;
            $media->folder = rtrim($mediaPath, '/');
            $media->save();

            return $normalizedPath;
        } catch (\Throwable $e) {
            \Log::error('Ошибка конвертации видео обложки челленджа: ' . $e->getMessage());
            return $sourcePath;
        }
    }

    private function generateCoverThumbnailFromVideo($media, string $videoPath, string $prefix, int $id): void
    {
        $filePath = 'uploads/stories/thumbs/' . $prefix . '_' . $id . '/';
        $path = public_path($filePath);
        if (!File::isDirectory($path)) {
            File::makeDirectory($path, 0777, true, true);
        }

        $fileName = 'thumb_' . $media->slug . '.jpg';
        if (!file_exists($path . $fileName)) {
            $videoThumbnail = new VideoThumbnail();
            $videoThumbnail->createThumbnail($videoPath, $path, $fileName, 0, 607, 1080);
        }

        $media->thumbnail = $filePath . $fileName;
        $media->save();
    }

    private function generateCoverThumbnailFromImage($media, string $sourcePath, string $prefix, int $id): void
    {
        $filePath = 'uploads/stories/thumbs/' . $prefix . '_' . $id . '/';
        $path = public_path($filePath);
        if (!File::isDirectory($path)) {
            File::makeDirectory($path, 0777, true, true);
        }

        $fileName = 'thumb_' . $media->slug . '.jpg';
        Image::make($sourcePath)
            ->fit(607, 1080, null, 'center')
            ->encode('jpg', 90)
            ->save($path . $fileName);

        $media->thumbnail = $filePath . $fileName;
        $media->save();
    }

    private function filterChallengeColumns(array $data): array
    {
        foreach (['reward_amount', 'winner_selection', 'invite_user_ids'] as $column) {
            if (!\Illuminate\Support\Facades\Schema::hasColumn('challenges', $column)) {
                unset($data[$column]);
            }
        }

        return $data;
    }

    private function parseVisualDate(?string $value, ?Carbon $default = null): ?Carbon
    {
        $value = trim((string) $value);
        if ($value === '') {
            return $default;
        }

        return Carbon::createFromFormat('d.m.y H:i', $value);
    }

    private function validateVisualDates(Request $request, ?Challenge $challenge = null)
    {
        $errors = validator([], [])->errors();
        $now = Carbon::now()->startOfMinute();
        $dateFrom = $this->parseVisualDate($request->input('date_from_visual'));
        $dateTo = $this->parseVisualDate($request->input('date_to_visual'));
        $originalDateFrom = $challenge && $challenge->date_from ? $challenge->date_from->format('d.m.y H:i') : null;
        $originalDateTo = $challenge && $challenge->date_to ? $challenge->date_to->format('d.m.y H:i') : null;
        $dateFromChanged = !$challenge || trim((string) $request->input('date_from_visual')) !== $originalDateFrom;
        $dateToChanged = !$challenge || trim((string) $request->input('date_to_visual')) !== $originalDateTo;

        if ($dateFromChanged && $dateFrom && $dateFrom->lte($now)) {
            $errors->add('date_from_visual', 'Дата и время начала должны быть позже текущего времени');
        }
        if ($dateToChanged && $dateTo && $dateTo->lt($now)) {
            $errors->add('date_to_visual', 'Дата и время окончания не могут быть в прошлом');
        }
        elseif ($challenge && $dateToChanged && $dateTo && $dateTo->lt($now->copy()->addHours(12))) {
            $errors->add('date_to_visual', 'До окончания челленджа должно оставаться не менее 12 часов');
        }
        if ($dateFrom && $dateTo && $dateTo->lt($dateFrom)) {
            $errors->add('date_to_visual', 'Дата и время окончания не могут быть раньше начала');
        }

        return $errors;
    }

    private function mergeExistingInviteUserIds(Challenge $challenge, array $inviteUserIds): array
    {
        return collect($challenge->invite_user_ids ?? [])
            ->map(fn ($id) => (int) $id)
            ->merge($inviteUserIds)
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }

    private function validateParticipantsEdit(Challenge $challenge, ?int $participantsCount)
    {
        $errors = validator([], [])->errors();
        $hasStarted = (bool) ($challenge->started ?? false);
        $originalCount = (int) ($challenge->participants_count ?? 0);
        $originalMode = $originalCount === 1 ? '1' : ($originalCount > 1 ? 'limit' : '0');
        $newMode = $participantsCount === 1 ? '1' : ($participantsCount > 1 ? 'limit' : '0');
        $actualParticipants = $this->actualChallengeParticipantsCount($challenge);

        if ($hasStarted && ($newMode !== $originalMode || (int) $participantsCount !== $originalCount)) {
            $errors->add('participants_visual', 'Число участников нельзя менять после начала челленджа');
            return $errors;
        }

        if ($newMode === $originalMode && (int) $participantsCount === $originalCount) {
            return $errors;
        }

        if ($originalMode === '1') {
            return $errors;
        }

        if ($originalMode === '0') {
            if ($actualParticipants <= 1) {
            } elseif ($newMode !== 'limit' || (int) $participantsCount < $actualParticipants) {
                $errors->add('participants_visual', 'Можно сменить только на лимит не меньше текущего числа участников');
            }
            return $errors;
        }

        if ($actualParticipants <= 1) {
        } elseif ($newMode !== '0' && ($newMode !== 'limit' || (int) $participantsCount < $actualParticipants)) {
            $errors->add('participants_visual', 'Лимит участников не может быть меньше текущего числа участников');
        }

        return $errors;
    }

    private function validateStartedFieldChanges(Challenge $challenge, Request $request)
    {
        $errors = validator([], [])->errors();
        $hasStarted = (bool) ($challenge->started ?? false);
        $startsAt = $challenge->date_from ?: $challenge->start;
        $requestedStartsAt = trim((string) $request->input('date_from_visual'));
        if ($requestedStartsAt !== '') {
            $startsAt = Carbon::createFromFormat('d.m.y H:i', $requestedStartsAt);
        }
        $settingsLocked = $hasStarted
            || ($startsAt && Carbon::parse($startsAt)->lte(Carbon::now()));
        $winnerSelectionChanged = (string) ($request->input('winner_selection') ?: 'likes')
            !== (string) ($challenge->winner_selection ?: 'likes');
        $rhythmChanged = (string) ($request->input('rhythm_visual') ?: 'daily')
            !== (string) ($challenge->rhythm ?: 'daily');
        $checkinChanged = (string) ($request->input('checkin_visual') ?: 'story')
            !== (string) ($challenge->checkin ?: 'story');
        $requestedReward = $request->filled('reward_amount') ? (int) $request->input('reward_amount') : null;
        $currentReward = $challenge->reward_amount ? (int) $challenge->reward_amount : null;

        if ($settingsLocked && $winnerSelectionChanged) {
            $errors->add('winner_selection', 'Выбор победителя нельзя менять после начала челленджа');
        }
        if ($settingsLocked && $rhythmChanged) {
            $errors->add('rhythm_visual', 'Ритм нельзя менять после начала челленджа');
        }
        if ($settingsLocked && $checkinChanged) {
            $errors->add('checkin_visual', 'Чек-ин нельзя менять после начала челленджа');
        }
        if ($settingsLocked && $requestedReward !== $currentReward) {
            $errors->add('reward_amount', 'Награду нельзя менять после начала челленджа');
        }

        if (!$hasStarted) {
            return $errors;
        }

        $dateFromChanged = trim((string) $request->input('date_from_visual')) !== ($challenge->date_from ? $challenge->date_from->format('d.m.y H:i') : null);
        $dateToChanged = trim((string) $request->input('date_to_visual')) !== ($challenge->date_to ? $challenge->date_to->format('d.m.y H:i') : null);

        if ($dateFromChanged || $dateToChanged) {
            $errors->add('date_from_visual', 'Срок нельзя менять после начала челленджа');
        }
        return $errors;
    }

    private function actualChallengeParticipantsCount(Challenge $challenge): int
    {
        $ids = $challenge->stories()
            ->withoutGlobalScopes()
            ->pluck('user_id')
            ->filter()
            ->map(fn ($id) => (int) $id);

        return $ids->unique()->count();
    }

    public function get_file($id)
    {
        $challenge = Challenge::find($id);
        if (!$challenge || !app(ContestVisibilityService::class)->canView($challenge, Auth::user() ?? auth()->user())) {
            abort(404);
        }
        $story = $challenge ? $challenge->getMainStory()->first() : null;
        $media = $story ? $story->media : null;
        if (!$media && $challenge) {
            $media = $challenge->media;
        }
        if (!$media) {
            abort(404);
        }

        $file_path = 'uploads/challenges/';
        if($media->folder) {
            $file_path = rtrim($media->folder, '/') . '/';
        }
        $videoUrl = public_path($file_path . $media->slug_ext);

        if ($challenge && file_exists($videoUrl)) {
//            \RB\HTTP\Files\Download::init(array('speed_limit' => 0, 'data_dir' => public_path($file_path)));
            \RB\HTTP\Files\Download::init(array('speed_limit' => 0, 'chunksize' => 65536, 'enable_partial' => true, 'data_dir' => public_path($file_path)));
            \RB\HTTP\Files\Download::get_file($media->slug_ext);
        }


    }

    public function get(Request $request, $id, $only_body = false, $donate = true)
    {
        $challenge = Challenge::find($id);
        if (!$challenge) {
            return response()->json([
                'success' => false,
                'error' => 'Челлендж не найдена'
            ]);
        }
        if (!app(ContestVisibilityService::class)->canView($challenge, Auth::user() ?? auth()->user())) {
            return response()->json([
                'success' => false,
                'error' => 'Просмотр недоступен',
            ], 403);
        }
        $formatted = app(ContestDetailFormatter::class)->formatChallenge($challenge, $request);
        if ($formatted['user_missing']) {
            return response()->json([
                'success' => false,
                'error' => 'Пользователь не найден'
            ]);
        }

        $data = $formatted['data'];

        if ($only_body) {
            return $data;
        }

        if ($formatted['blocked']) {
            return response()->json([
                'success' => false,
                'error' => 'Просмотр недоступен'
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $data
        ]);

    }

    public function getPreview(Request $request, $id)
    {
        $data = $this->get($request, $id, true, false);

        $user_id = $request->input('user_id');

        $user = null;
        $auth_user = null;
        if ($user_id) {
            $user = User::find($user_id);
            $auth_user = $user;
        }
        $view = view('challenges.modal_content', compact('data', 'user', 'auth_user'))->render();
        return response()->json([
            'success' => true,
            'data' => $view
        ]);
    }

    public function account_info($id = null, $only_data = false, $just_user_info = false)
    {
        $userData = app(ApiAccountInfoService::class)->build($id, (bool) $just_user_info);

        if (!$userData) {
            return response()->json([
                'success' => false,
                'error' => 'User ID ' . $id . ' not found'
            ]);
        }

        if ($only_data) {
            return $userData;
        }

        return response()->json([
            'success' => true,
            'data' => $userData
        ]);
    }

    public function challenges_list(Request $request)
    {
        $title = 'Модерация челленджей';

        $user = Auth::user();

        $challenges = [];

        $type = $request->input('type');
        $challenge_id = $request->input('challenge_id');
        $ai_moderated = $request->input('ai_moderated');


        if ($user->is_admin() || $user->is_comment_admin()) {
            $data_query = Challenge::query();
            if ($request->input('story_id')) {
                $data_query->where('id', $request->input('story_id'));
            }

            if ($type == 'declined') {
                $data_query->where('declined', true);
            } elseif ($type == 'active') {
                $data_query->where('active', true)->where('declined', false);
            } elseif ($type == 'blocked') {
                $data_query->whereNotNull('blocked_at');
            } else {
                $data_query->where('active', false)->where('declined', false);
            }

            if ($challenge_id) {
                $data_query->orWhere('id', $challenge_id);
            }

            if ($ai_moderated) {
                $data_query->where('ai_moderated', true);
            }

            $challenges = $data_query->orderBy('id', 'desc')->paginate(12);
        }


        return view('admin.challenges', compact('title', 'challenges'));
    }

    public function confirm(Request $request)
    {
        $user = Auth::user();

        // Preventing unauthorised action
        $challenge = Challenge::find($request->challenge_id);

        if ($user->id != $challenge->user_id && !$user->is_admin() && !$user->is_comment_admin()) {
            return ['success' => false];
        }

        $story_user = $challenge->user;
        $helper = new AppHelper();
        switch ($request->action) {
            case 'approve':
                $helper->challenge_approve($challenge);
                break;

            case 'restart':
                $helper->challenge_restart($challenge);
                break;

            case 'trash':
                $helper->challenge_decline($challenge);
                break;
        }

        return ['success' => 1];
    }

    public function remove(Request $request)
    {
        $user = Auth::user() ?? auth()->user() ?? null;
        // Preventing unauthorised action
        $challenge = Challenge::find($request->challenge_id);

        if (!$user) {
            return ['success' => false, 'error' => 'Пользователь не найден'];
        }

        if (!$challenge) {
            return ['success' => false, 'error' => 'Челлендж не найден'];
        }

        if ($user->id != $challenge->user_id && !$user->is_admin() && !$user->is_comment_admin()) {
            return ['success' => false, 'error' => 'Вы не можете удалить этот челлендж'];
        }
        $challenge->delete();
        return ['success' => true, 'message' => 'Челлендж удален'];
    }

    public function selectWinners(Request $request, ChallengeWinnerSelectionService $winnerService)
    {
        $user = Auth::user() ?? auth()->user() ?? null;
        $challenge = Challenge::find($request->input('challenge_id'));

        if (!$user) {
            return response()->json(['success' => false, 'error' => 'Пользователь не найден'], 401);
        }

        if (!$challenge) {
            return response()->json(['success' => false, 'error' => 'Челлендж не найден'], 404);
        }

        if ((int) $user->id !== (int) $challenge->user_id && !$user->is_admin() && !$user->is_comment_admin()) {
            return response()->json(['success' => false, 'error' => 'Вы не можете выбрать победителя этого челленджа'], 403);
        }

        if ($challenge->winner_selection !== 'creator') {
            return response()->json(['success' => false, 'error' => 'Для этого челленджа не выбран ручной выбор победителя'], 422);
        }

        if ($challenge->winner_selection_status !== 'pending') {
            return response()->json(['success' => false, 'error' => 'Выбор победителя уже завершен'], 422);
        }

        if ($challenge->winner_selection_deadline && Carbon::parse($challenge->winner_selection_deadline)->isPast()) {
            return response()->json(['success' => false, 'error' => 'Срок ручного выбора победителя истек'], 422);
        }

        $winnerUserIds = $request->input('winner_user_ids', $request->input('user_ids', []));
        if (!is_array($winnerUserIds)) {
            $winnerUserIds = [$winnerUserIds];
        }

        $winnerUserIds = array_values(array_unique(array_filter(array_map('intval', $winnerUserIds))));

        if (!$winnerUserIds) {
            return response()->json(['success' => false, 'error' => 'Выберите победителя'], 422);
        }

        $eligibleUserIds = $winnerService->eligibleWinnerUserIds($challenge);
        $invalidUserIds = array_values(array_diff($winnerUserIds, $eligibleUserIds));

        if ($invalidUserIds) {
            return response()->json([
                'success' => false,
                'error' => 'Победителем можно выбрать только участника челленджа',
                'invalid_user_ids' => $invalidUserIds,
            ], 422);
        }

        $result = $winnerService->finishByCreator($challenge, $winnerUserIds, (int) $user->id);

        app(ContestNotificationService::class)->results(
            $challenge,
            'challenge',
            $result['stories'],
            $result['winner_story_ids'],
            $result['prize'],
            $result['winner_user_ids']
        );

        return response()->json([
            'success' => true,
            'winner_user_ids' => $winnerUserIds,
            'prize' => $result['prize'],
        ]);
    }

}
