<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\CampaignsController;
use App\Http\Controllers\Controller;
use App\Http\Requests\DeelsCampaignStoreRequest;
use App\Models\Campaign;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeelsCampaignCompatibilityController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if (!$request->filled('type')) {
            $request->merge(['type' => 'all']);
        }

        $legacy = app(CampaignsController::class)->index(
            $request,
            app(\App\Services\CampaignFilterService::class)
        );

        if (!$legacy instanceof JsonResponse) {
            return response()->json([
                'success' => false,
                'message' => 'Не удалось получить список копилок',
            ], 500);
        }

        $payload = $legacy->getData(true);
        $paginator = $payload['campaigns'] ?? [];
        $rows = collect($paginator['data'] ?? []);
        $ids = $rows->pluck('id')->filter()->map(fn($id) => (int) $id)->all();

        $models = Campaign::query()
            ->with(['user', 'feature_media'])
            ->withSum('success_payments', 'amount')
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');

        $items = $rows->map(function (array $row) use ($models): array {
            $campaign = $models->get((int) ($row['id'] ?? 0));
            return $campaign ? $this->normalize($campaign) : $row;
        })->values();

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $items,
                'total' => (int) ($paginator['total'] ?? $items->count()),
                'next_page' => isset($paginator['current_page'], $paginator['last_page'])
                    && (int) $paginator['current_page'] < (int) $paginator['last_page']
                        ? (int) $paginator['current_page'] + 1
                        : null,
            ],
            // Preserve the legacy shape for diagnostics/backward compatibility.
            'campaigns' => $paginator,
            'filteredCategory' => $payload['filteredCategory'] ?? null,
        ]);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $campaign = Campaign::query()
            ->with(['user', 'feature_media'])
            ->withSum('success_payments', 'amount')
            ->where(function ($query) use ($id): void {
                $query->where('slug', $id);
                if (ctype_digit($id)) {
                    $query->orWhere('id', (int) $id);
                }
            })
            ->where('status', 1)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $this->normalize($campaign),
        ]);
    }

    public function store(DeelsCampaignStoreRequest $request): JsonResponse
    {
        $beforeId = (int) Campaign::where('user_id', $request->user()->id)->max('id');

        $legacy = app(CampaignsController::class)->store($request);
        if (!$legacy instanceof JsonResponse) {
            return response()->json([
                'success' => false,
                'message' => 'Не удалось создать копилку',
            ], 422);
        }

        $payload = $legacy->getData(true);
        if (empty($payload['success'])) {
            return response()->json([
                'success' => false,
                'message' => $payload['error'] ?? 'Не удалось создать копилку',
                'error' => $payload['error'] ?? null,
                'errors' => $payload['errors'] ?? [],
            ], 422);
        }

        $campaign = Campaign::query()
            ->with(['user', 'feature_media'])
            ->withSum('success_payments', 'amount')
            ->where('user_id', $request->user()->id)
            ->where('id', '>', $beforeId)
            ->where('title', (string) $request->input('title'))
            ->latest('id')
            ->first();

        if (!$campaign) {
            return response()->json([
                'success' => false,
                'message' => 'Копилка создана, но не удалось получить её данные',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'data' => $this->normalize($campaign),
        ], 201);
    }

    private function normalize(Campaign $campaign): array
    {
        $raised = (float) ($campaign->success_payments_sum_amount ?? 0);
        $goal = max(1.0, (float) $campaign->goal);
        $user = $campaign->user;

        return [
            'id' => $campaign->id,
            'slug' => $campaign->slug,
            'title' => $campaign->title,
            'description' => $campaign->description,
            'short_description' => $campaign->short_description,
            'goal' => (float) $campaign->goal,
            'raised_amount' => $raised,
            'progress_percent' => min(100, round(($raised / $goal) * 100, 2)),
            'media_url' => $campaign->feature_img_url()->feature_image,
            'verified' => (int) $campaign->status === 1,
            'organizer_name' => $user?->username ?: $user?->name,
            'user' => $user,
            'status' => $campaign->status,
            'created_at' => $campaign->created_at,
            'end_date' => $campaign->end_date,
        ];
    }
}
