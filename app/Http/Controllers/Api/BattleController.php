<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Http\Controllers\MediaController;
use App\Jobs\FinishBattle;
use App\Models\Abuse;
use App\Models\Battle;
use App\Models\Story;
use App\Models\User;
use App\Rules\MaxWords;
use App\Models\View;
use App\Services\ApiAccountInfoService;
use App\Services\ApiStoryFeedFormatter;
use App\Services\Contests\ContestDetailFormatter;
use App\Services\Contests\ContestListService;
use App\Services\Contests\ContestNotificationService;
use App\Services\Contests\ContestVisibilityService;
use Carbon\Carbon;
use FFMpeg\FFMpeg;
use FFMpeg\Coordinate\TimeCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;
use Pawlox\VideoThumbnail\VideoThumbnail;

class BattleController extends Controller
{

    public function get_battles(Request $request)
    {
        $listService = app(ContestListService::class);
        $media = $listService->battles($request);

        if (request()->wantsJson()) {
            $payload = $listService->formatPaginator($media);

            return response()->json([
                'success' => true,
                'data' => $payload['data'],
            ]);
        }

        return view('challenges_index', ['challenges' => $media]);
    }

    public function get_popular_answers(Request $request)
    {
        $user_id = $request->input('user_id');
        $media_query = Story::withoutGlobalScopes()->with('comments', 'likes')
            ->whereNotNull('battle_id')
            ->notUseful()
            ->where('active', true)
            ->where('declined', false)
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
        $battle = Battle::find($id);
        if (!$battle) {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Батл не найден'
                ]);
            } else {
                abort(404);
            }
        }

        if (!app(ContestVisibilityService::class)->canView($battle, Auth::user() ?? auth()->user())) {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Просмотр недоступен',
                ], 403);
            }

            return response()->view('contests.private', ['contest' => $battle]);
        }

        if (!$battle->active || $battle->declined) {
            $viewer = Auth::user();
            $canViewInactive = $viewer
                && ($viewer->is_admin() || (int) $battle->user_id === (int) $viewer->id);

            if (!$canViewInactive) {
                if (request()->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Батл не найден'
                    ]);
                } else {
                    abort(404);
                }
            }
        }

        $title = 'Батл ' . $battle->title;
        $stories = $battle->stories()->active()->orderByDesc('is_main_story')->latest()->paginate(500);
        $stories_count = $battle->stories()->active()->count();
        return view('battles.page_front', compact('battle', 'title', 'stories', 'stories_count'));
    }

    public function store_web(Request $request)
    {
        $challange_coin = false;
        $rules = [
            'amount' => $challange_coin ? 'required|numeric|min:100|max:10000' : 'nullable|numeric|min:0|max:10000',
            'title' => 'required',
            'description' => ['required', 'string', new MaxWords(650)],
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
            'rhythm_visual' => 'nullable|in:once,daily,three_days',
            'visibility' => 'nullable|in:all,friends,participants',
            'invite_user_ids' => 'nullable|array',
            'invite_user_ids.*' => 'integer|exists:users,id',
            'called_user_id' => 'required|integer|exists:users,id',
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

        if ($request->input('battle_id')) {
            $battle = Battle::find($request->input('battle_id'));
            $error_message = null;
            if (!$battle || ($battle && $battle->user_id != Auth::user()->id && !Auth::user()->is_admin())) {
                $error_message = 'Вы не можете редактировать этот батл';
            }
            if ($battle && $battle->finished) {
                $error_message = 'Батл уже завершен';
            }
            if ($battle && $battle->declined) {
                $error_message = 'Батл отклонен';
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
        if ($request->get('days') && $request->get('days') > 0) {
            unset($rules['date_to']);
        }
        $image = '';
        $images = [];
        $file = $request->file('mainImg') ?? $request->file('file') ?? [];
        $validator = validator($request->all(), $rules);
        $requiresCover = !isset($battle) || !$battle->media_id;

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
        $visualDateErrors = $this->validateVisualDates($request, $battle ?? null);
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
            $storedData = (new MediaController())->store($request, 'battles');
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
        if (isset($battle)) {
            $invite_user_ids = $this->mergeExistingInviteUserIds($battle, $invite_user_ids);
            $participantsErrors = $this->validateParticipantsEdit($battle, $participants_count);
            $startedFieldErrors = $this->validateStartedFieldChanges($battle, $request);
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
        $called_user_id = $request->filled('called_user_id')
            ? (int) $request->input('called_user_id')
            : null;
        if ($called_user_id === (int) (auth()->id() ?? $request->user_id)) {
            $called_user_id = null;
        }

        if ($participants_count === 1) {
            $reward_amount = null;
            $winner_selection = null;
            $invite_user_ids = [];
            $called_user_id = null;
        } elseif ($winner_selection === 'creator') {
            $winner_selection = 'likes';
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
            'user_id' => $battle->user_id ?? auth()->id() ?? $request->user_id,
            'title' => $request->title,
            'description' => $request->description,
            'amount' => $request->amount,
            'reward_amount' => $reward_amount,
            'start' => null,
            'days' => $days,
            'min_participants' => $min_participants,
            'participants_count' => $participants_count,
            'visibility' => $request->input('visibility'),
            'rhythm' => $request->input('rhythm_visual') ?: 'daily',
            'checkin' => $request->input('checkin_visual'),
            'winner_selection' => $winner_selection,
            'invite_user_ids' => $invite_user_ids,
            'called_user_id' => $called_user_id,
            'date_from' => $date_from,
            'date_to' => $date_to,
            'cost' => $cost ?? 0,
            'active' => true,
            'finish' => $finish_date,
            'media_id' => $media_id ?? $battle->media_id ?? null,
            'by_views' => in_array('by_views', $request->criteria),
            'by_likes' => in_array('by_likes', $request->criteria),
            'by_comments' => in_array('by_comments', $request->criteria),
        ];
        $data = $this->filterBattleColumns($data);
        if (isset($battle)) {
            $data['active'] = 1;
            $battle->update($data);
            $create = $battle;
            if ($called_user_id) {
                DB::table('contest_participations')
                    ->where([
                        'contest_type' => 'battle',
                        'contest_id' => $create->id,
                        'user_id' => $called_user_id,
                        'status' => 'declined',
                    ])
                    ->delete();
            }
            app(ContestNotificationService::class)->battleUpdated($create);
        } else {
            if ($challange_coin && intval($request->amount) > 0) {
                $payments_wallet = $user->getWallet('payments');
                try {
                    $payments_wallet->withdraw(intval($request->amount), ['create' => 'battle', 'description' => 'Оплата за создание батла']);
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
            $create = Battle::create($data);
            app(ContestNotificationService::class)->battleModerated($create);
            if ($create->user_id) {
                DB::table('contest_participations')->updateOrInsert(
                    [
                        'contest_type' => 'battle',
                        'contest_id' => $create->id,
                        'user_id' => $create->user_id,
                    ],
                    [
                        'status' => 'active',
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
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
            $this->processCoverMedia($create, 'battle');
        }

        if ($request->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'battle_id' => $create->id,
                'clear_challenge_create_draft' => !isset($battle),
            ]);
        } else {
            $success_message = isset($battle) ? 'Батл изменен!' : 'Батл создан!';
            if (isset($battle)) {
                return redirect()
                    ->route('dashboard_battle_page', $create->id)
                    ->with('success', $success_message);
            }

            return redirect()
                ->route('battle_page', $create->id)
                ->with('success', $success_message)
                ->with('clear_challenge_create_draft', true);
        }

    }

    private function replaceMainStory(Request $request, Battle $battle): ?Story
    {
        $oldStory = $battle->getMainStory()->first();
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
            'user_id' => $battle->user_id,
        ]);
        $storyRequest->setUserResolver(fn () => $request->user());
        $storyRequest->files->set('files', [$file]);

        $storedData = (new MediaController())->store($storyRequest);
        if (empty($storedData['success']) || empty($storedData['images'][0])) {
            return null;
        }

        $story = Story::withoutGlobalScopes()->create([
            'user_id' => $battle->user_id,
            'description' => $battle->description,
            'amount' => 0,
            'paid' => false,
            'active' => true,
            'declined' => false,
            'battle_id' => $battle->id,
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
            $mediaPath = $media->folder ? rtrim($media->folder, '/') . '/' : 'uploads/battles/';
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
            \Log::error('Ошибка генерации видео превью батла: ' . $e->getMessage());
        }
    }

    private function processCoverMedia(Battle $battle, string $prefix): void
    {
        $media = $battle->media;
        if (!$media) {
            return;
        }

        $mediaPath = $media->folder ? rtrim($media->folder, '/') . '/' : 'uploads/battles/';
        $filePath = public_path($mediaPath . $media->slug_ext);
        if (!file_exists($filePath)) {
            return;
        }

        if ($battle->type === 'video') {
            $filePath = $this->normalizeCoverVideo($media, $filePath, $mediaPath);
            $this->generateCoverThumbnailFromVideo($media, $filePath, $prefix, $battle->id);
            $this->generateVideoPreview($media, $filePath);
            return;
        }

        $this->generateCoverThumbnailFromImage($media, $filePath, $prefix, $battle->id);
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
            \Log::error('Ошибка конвертации видео обложки батла: ' . $e->getMessage());
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

    private function filterBattleColumns(array $data): array
    {
        foreach (['reward_amount', 'winner_selection', 'invite_user_ids', 'called_user_id', 'rhythm'] as $column) {
            if (!\Illuminate\Support\Facades\Schema::hasColumn('battles', $column)) {
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

    private function validateVisualDates(Request $request, ?Battle $battle = null)
    {
        $errors = validator([], [])->errors();
        $now = Carbon::now()->startOfMinute();
        $dateFrom = $this->parseVisualDate($request->input('date_from_visual'));
        $dateTo = $this->parseVisualDate($request->input('date_to_visual'));
        $originalDateFrom = $battle && $battle->date_from ? $battle->date_from->format('d.m.y H:i') : null;
        $originalDateTo = $battle && $battle->date_to ? $battle->date_to->format('d.m.y H:i') : null;
        $dateFromChanged = !$battle || trim((string) $request->input('date_from_visual')) !== $originalDateFrom;
        $dateToChanged = !$battle || trim((string) $request->input('date_to_visual')) !== $originalDateTo;

        if ($dateFromChanged && $dateFrom && $dateFrom->lte($now)) {
            $errors->add('date_from_visual', 'Дата и время начала должны быть позже текущего времени');
        }
        if ($dateToChanged && $dateTo && $dateTo->lt($now)) {
            $errors->add('date_to_visual', 'Дата и время окончания не могут быть в прошлом');
        }
        elseif ($battle && $dateToChanged && $dateTo && $dateTo->lt($now->copy()->addHours(12))) {
            $errors->add('date_to_visual', 'До окончания батла должно оставаться не менее 12 часов');
        }
        if ($dateFrom && $dateTo && $dateTo->lt($dateFrom)) {
            $errors->add('date_to_visual', 'Дата и время окончания не могут быть раньше начала');
        }

        return $errors;
    }

    private function mergeExistingInviteUserIds(Battle $battle, array $inviteUserIds): array
    {
        return collect($battle->invite_user_ids ?? [])
            ->map(fn ($id) => (int) $id)
            ->merge($inviteUserIds)
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }

    private function validateParticipantsEdit(Battle $battle, ?int $participantsCount)
    {
        $errors = validator([], [])->errors();
        $hasStarted = (bool) ($battle->started ?? false);

        if ($hasStarted && (int) $participantsCount !== (int) ($battle->participants_count ?? 2)) {
            $errors->add('participants_visual', 'Число участников нельзя менять после начала батла');
        }

        return $errors;
    }

    private function validateStartedFieldChanges(Battle $battle, Request $request)
    {
        $errors = validator([], [])->errors();
        $hasStarted = (bool) ($battle->started ?? false);
        $startsAt = $battle->date_from ?: $battle->start;
        $requestedStartsAt = trim((string) $request->input('date_from_visual'));
        if ($requestedStartsAt !== '') {
            $startsAt = Carbon::createFromFormat('d.m.y H:i', $requestedStartsAt);
        }
        $settingsLocked = $hasStarted
            || ($startsAt && Carbon::parse($startsAt)->lte(Carbon::now()));
        $winnerSelectionChanged = (string) ($request->input('winner_selection') ?: 'likes')
            !== (string) ($battle->winner_selection ?: 'likes');
        $rhythmChanged = (string) ($request->input('rhythm_visual') ?: 'daily')
            !== (string) ($battle->rhythm ?: 'daily');
        $checkinChanged = (string) ($request->input('checkin_visual') ?: 'story')
            !== (string) ($battle->checkin ?: 'story');
        $requestedReward = $request->filled('reward_amount') ? (int) $request->input('reward_amount') : null;
        $currentReward = $battle->reward_amount ? (int) $battle->reward_amount : null;

        if ($settingsLocked && $winnerSelectionChanged) {
            $errors->add('winner_selection', 'Выбор победителя нельзя менять после начала батла');
        }
        if ($settingsLocked && $rhythmChanged) {
            $errors->add('rhythm_visual', 'Ритм нельзя менять после начала батла');
        }
        if ($settingsLocked && $checkinChanged) {
            $errors->add('checkin_visual', 'Чек-ин нельзя менять после начала батла');
        }
        if ($settingsLocked && $requestedReward !== $currentReward) {
            $errors->add('reward_amount', 'Награду нельзя менять после начала батла');
        }

        if (!$hasStarted) {
            return $errors;
        }

        $dateFromChanged = trim((string) $request->input('date_from_visual')) !== ($battle->date_from ? $battle->date_from->format('d.m.y H:i') : null);
        $dateToChanged = trim((string) $request->input('date_to_visual')) !== ($battle->date_to ? $battle->date_to->format('d.m.y H:i') : null);

        if ($dateFromChanged || $dateToChanged) {
            $errors->add('date_from_visual', 'Срок нельзя менять после начала батла');
        }
        return $errors;
    }

    public function get_file($id)
    {
        $battle = Battle::find($id);
        if (!$battle || !app(ContestVisibilityService::class)->canView($battle, Auth::user() ?? auth()->user())) {
            abort(404);
        }
        $story = $battle ? $battle->getMainStory()->first() : null;
        $media = $story ? $story->media : null;
        if (!$media && $battle) {
            $media = $battle->media;
        }
        if (!$media) {
            abort(404);
        }

        $file_path = 'uploads/battles/';
        if ($media->folder) {
            $file_path = rtrim($media->folder, '/') . '/';
        }
        $videoUrl = public_path($file_path . $media->slug_ext);

        if ($battle && file_exists($videoUrl)) {
//            \RB\HTTP\Files\Download::init(array('speed_limit' => 0, 'data_dir' => public_path($file_path)));
            \RB\HTTP\Files\Download::init(array('speed_limit' => 0, 'chunksize' => 65536, 'enable_partial' => true, 'data_dir' => public_path($file_path)));
            \RB\HTTP\Files\Download::get_file($media->slug_ext);
        }


    }

    public function get(Request $request, $id, $only_body = false, $donate = true)
    {
        $battle = Battle::find($id);
        if (!$battle) {
            return response()->json([
                'success' => false,
                'error' => 'Батл не найдена'
            ]);
        }
        if (!app(ContestVisibilityService::class)->canView($battle, Auth::user() ?? auth()->user())) {
            return response()->json([
                'success' => false,
                'error' => 'Просмотр недоступен',
            ], 403);
        }
        $formatted = app(ContestDetailFormatter::class)->formatBattle($battle, $request);
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
        if (view()->exists('battles.modal_content')) {
            $view = view('battles.modal_content', compact('data', 'user', 'auth_user'))->render();
        } else {
            $view = null;
        }

        return response()->json([
            'success' => true,
            'data' => $view,
            'battle' => $data,
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

    public function battles_list(Request $request)
    {
        $title = 'Модерация батлов';

        $user = Auth::user();

        $battles = [];

        $type = $request->input('type');
        $battle_id = $request->input('battle_id');
        $ai_moderated = $request->input('ai_moderated');


        if ($user->is_admin() || $user->is_comment_admin()) {
            $data_query = Battle::query();
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

            if ($battle_id) {
                $data_query->orWhere('id', $battle_id);
            }

            if ($ai_moderated) {
                $data_query->where('ai_moderated', true);
            }

            $battles = $data_query->orderBy('id', 'desc')->paginate(12);
        }


        return view('admin.battles', compact('title', 'battles'));
    }

    public function confirm(Request $request)
    {
        $user = Auth::user();

        // Preventing unauthorised action
        $battle = Battle::find($request->battle_id);

        if ($user->id != $battle->user_id && !$user->is_admin() && !$user->is_comment_admin()) {
            return ['success' => false];
        }

        switch ($request->action) {
            case 'delete':
                $battle->delete();
                break;
            case 'finish':
                FinishBattle::dispatch($battle);
                break;
        }

        return ['success' => 1];
    }

    public function remove(Request $request)
    {
        $user = Auth::user() ?? auth()->user() ?? null;
        // Preventing unauthorised action
        $battle = Battle::find($request->battle_id);

        if (!$user) {
            return ['success' => false, 'error' => 'Пользователь не найден'];
        }

        if (!$battle) {
            return ['success' => false, 'error' => 'Батл не найден'];
        }

        if ($user->id != $battle->user_id && !$user->is_admin() && !$user->is_comment_admin()) {
            return ['success' => false, 'error' => 'Вы не можете удалить этот батл'];
        }
        $battle->delete();
        return ['success' => true, 'message' => 'Батл удален'];
    }

    public function battles_stories_list(Request $request)
    {
        $title = 'Модерация ответов на батлы';

        $user = Auth::user();

        $stories = [];

        $type = $request->input('type');
        $ai_moderated = $request->input('ai_moderated');
        $story_id = $request->input('story_id');
        $battle_id = $request->input('battle_id');


        if ($user->is_admin() || $user->is_comment_admin()) {
            $stories_query = Story::query()
                ->withoutGlobalScopes()
                ->whereNotNull('battle_id')
                ->where(function ($query): void {
                    $query->where('is_main_story', false)
                        ->orWhereNull('is_main_story');
                });
            if ($request->input('story_id')) {
                $stories_query->where('id', $story_id);
            }
            if ($request->input('challenge_id')) {
                $stories_query->where('challenge_id', $battle_id);
            }


            if ($type == 'frozen') {
                $stories_query->where('frozen', true)->where('banned', false);
            } elseif ($type == 'banned') {
                $stories_query->where('banned', true);
            }

            $stories = $stories_query->orderBy('id', 'desc')->paginate(12);
        }


        return view('admin.battle_stories', compact('title', 'stories'));
    }

    public function admin_battle_stories_confirm(Request $request)
    {
        $user = Auth::user();

        // Preventing unauthorised action
        $story = Story::withoutGlobalScopes()->find($request->story_id);

        if ($user->id != $story->user_id && !$user->is_admin() && !$user->is_comment_admin()) {
            return ['success' => false];
        }

        switch ($request->action) {
            case 'frozen':
                $story->update(['frozen' => true]);
                break;

            case 'banned':
                $story->update(['banned' => true, 'frozen' => false]);
                break;

            case 'approved':
                $story->update(['banned' => false, 'frozen' => false]);
                break;

        }

        return ['success' => 1];
    }

    public function get_stories(Request $request)
    {


        if ($request->session()->has('session_rand')) {
            if ((time() - $request->session()->get('session_rand')) > 3600) {
                $request->session()->put('session_rand', time());
            }
        } else {
            $request->session()->put('session_rand', time());
        }

        $popular = $request->input('popular');
        $filter_type = $request->input('type');
        $user_id = $request->input('user_id');
        $media_query = Story::withoutGlobalScopes()
            ->withCount('comments', 'likes')
            ->whereHas('user')
            ->where('active', true)
            ->whereNotNull('battle_id')
            ->where('declined', false)
            ->where(function ($query): void {
                $query->where('is_main_story', false)
                    ->orWhereNull('is_main_story');
            });

        $views = null;
        $user = Auth::user() ?? auth()->user() ?? null;
        if ($user_id) {
            $user = User::find($user_id);
        }

        $blocked_users = [];
        if ($user) {
            $views = View::select('story_id', DB::raw('COUNT(*) as view_count'))
                ->where('user_id', $user->id)
                ->groupBy('story_id')
                ->havingRaw('COUNT(*) > ?', [4])
                ->pluck('story_id')->toArray();
            $blocked_users = Abuse::where('abused_by', $user->id)->where('blocked', true)->pluck('user_id')->toArray();
        }


        if ($filter_type == 'popular') {
            $popular = true;
        }

        if ($filter_type == 'new') {
            $media_query->orderBy('created_at', 'DESC');
        }
        if ($filter_type == 'paid') {
            $media_query->where('paid', true)->where('amount', '>', 0);
        }
        if ($popular) {
            $media_query = Story::withoutGlobalScopes()->with('comments', 'likes')
                ->where('active', true)
                ->where('declined', false)
                ->whereNotNull('battle_id')
                ->notMainStory()
                ->withCount('comments', 'likes', 'views')
                ->orderBy('views_count', 'desc')
                ->orderBy('likes_count', 'desc')
                ->orderBy('comments_count', 'desc')
                ->orderBy('created_at', 'DESC');
        }

        if (!empty($blocked_users)) {
            $media_query->whereHas('user', function ($q) use ($blocked_users): void {
                $q->whereNotIn('id', $blocked_users);
            });
        }

        if (!$filter_type) {
//            $media_query->inRandomOrder();
            $media_query->orderBy(DB::raw('RAND(' . $request->session()->get('session_rand') . ')'));
        }

        if (!empty($views)) {
            $media_query->whereNotIn('id', $views);
        }

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

        return view('stories_index', ['stories' => $media]);

    }

}
