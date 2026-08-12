<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\SearchController;
use App\Mail\ContactUs;
use App\Models\Challenge;
use App\Services\ApiAccountInfoService;
use App\Services\Contests\ContestListService;
use App\Services\Contests\ContestVisibilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class DeelsUtilityCompatibilityController extends Controller
{
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        $data = app(ApiAccountInfoService::class)->build((int) $user->id, true) ?: $user->toArray();

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $validator = validator($request->all(), ['email' => ['required', 'email']]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Проверьте e-mail',
                'errors' => $validator->errors(),
            ], 422);
        }

        $status = Password::sendResetLink($request->only('email'));
        if ($status !== Password::RESET_LINK_SENT) {
            return response()->json([
                'success' => false,
                'message' => __($status),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => __($status),
        ]);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $validator = validator($request->all(), [
            'email' => ['required', 'email'],
            'token' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Проверьте заполненные поля',
                'errors' => $validator->errors(),
            ], 422);
        }

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            static function ($user, string $password): void {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
                $user->tokens()->delete();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return response()->json([
                'success' => false,
                'message' => __($status),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => __($status),
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $query = trim((string) $request->query('q', ''));
        if ($query === '') {
            return response()->json([
                'success' => true,
                'data' => [
                    'challenges' => [],
                    'stories' => [],
                    'campaigns' => [],
                    'users' => [],
                ],
            ]);
        }

        $request->merge(['q' => $query]);
        $request->headers->set('Accept', 'application/json');

        $legacy = app(SearchController::class)->search($request);
        $payload = $legacy instanceof JsonResponse ? $legacy->getData(true) : [];

        $challengeQuery = Challenge::query()
            ->where('challenges.active', 1)
            ->where('challenges.declined', 0)
            ->whereNull('challenges.blocked_at');

        app(ContestVisibilityService::class)->applyToContests(
            $challengeQuery,
            'challenges',
            Auth::user() ?? auth()->user()
        );

        $challengeQuery->where(function ($builder) use ($query): void {
            $builder
                ->where('title', 'like', '%' . $query . '%')
                ->orWhere('description', 'like', '%' . $query . '%')
                ->orWhereHas('user', function ($userQuery) use ($query): void {
                    $userQuery
                        ->where('username', 'like', '%' . $query . '%')
                        ->orWhere('name', 'like', '%' . $query . '%');
                });
        });

        $challengePage = $challengeQuery
            ->with('user')
            ->orderByDesc('created_at')
            ->paginate(20);
        $challengePayload = app(ContestListService::class)->formatPaginator($challengePage);

        return response()->json([
            'success' => true,
            'data' => [
                'challenges' => $challengePayload['data'] ?? [],
                'stories' => $payload['stories'] ?? [],
                'campaigns' => $payload['campaigns']['data'] ?? [],
                'users' => $payload['users']['data'] ?? [],
            ],
        ]);
    }

    public function media(Request $request): JsonResponse
    {
        $file = $request->file('media') ?? $request->file('file') ?? $request->file('mainImg');
        if (!$file) {
            return response()->json([
                'success' => false,
                'message' => 'Файл не передан',
                'errors' => ['media' => ['Добавьте файл']],
            ], 422);
        }

        $request->files->add(['files' => [$file]]);
        $stored = app(MediaController::class)->store($request);

        if (isset($stored['error'])) {
            return response()->json([
                'success' => false,
                'message' => (string) $stored['error'],
            ], 422);
        }

        $images = collect($stored['images'] ?? [])->map(static function ($media): array {
            return is_array($media) ? $media : [
                'id' => $media->id ?? null,
                'path' => $media->path ?? null,
                'url' => $media->url ?? null,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => $images,
        ]);
    }

    public function contact(Request $request): JsonResponse
    {
        $request->merge([
            'agreement' => $request->input('agreement', true),
            'subject' => $request->input('subject', 'Сообщение из контактной формы'),
        ]);

        $validator = validator($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'agreement' => ['accepted'],
            'message' => ['required', 'string', 'max:10000'],
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Проверьте заполненные поля',
                'errors' => $validator->errors(),
            ], 422);
        }

        if (Str::contains((string) $request->input('email'), 'godaddy') || $request->filled('lastname')) {
            return response()->json(['success' => true]);
        }

        try {
            Mail::send(new ContactUs($request));
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Не удалось отправить сообщение',
            ], 503);
        }

        return response()->json(['success' => true]);
    }
}
