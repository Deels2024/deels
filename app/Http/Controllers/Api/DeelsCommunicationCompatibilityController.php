<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeelsCommunicationCompatibilityController extends Controller
{
    public function sendMessage(Request $request, $id): JsonResponse
    {
        $validator = validator($request->all(), [
            'text' => ['required', 'string', 'max:5000'],
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Введите сообщение',
                'errors' => $validator->errors(),
            ], 422);
        }

        $request->merge([
            'thread_id' => (int) $id,
            'message' => trim((string) $request->input('text')),
        ]);

        $legacy = app(MessagesController::class)->send_message($request);
        $payload = $legacy->getData(true);

        if (empty($payload['success'])) {
            return response()->json($payload, $legacy->getStatusCode() >= 400 ? $legacy->getStatusCode() : 422);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'thread_id' => $payload['thread_id'] ?? (int) $id,
            ],
        ]);
    }

    public function notifications(Request $request): JsonResponse
    {
        $limit = max(1, min(50, (int) $request->input('limit', 20)));
        $query = UserEvent::query()
            ->where('user_id', (int) $request->user()->id)
            ->where(function ($builder): void {
                $builder->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->latest('created_at');

        $page = $query->paginate($limit);
        $rows = collect($page->items())->map(fn (UserEvent $event): array => $this->notificationRow($event))->values();

        return response()->json([
            'success' => true,
            'data' => $rows,
            'meta' => [
                'total' => $page->total(),
                'current_page' => $page->currentPage(),
                'next_page' => $page->hasMorePages() ? $page->currentPage() + 1 : null,
            ],
        ]);
    }

    public function readNotification(Request $request, $id): JsonResponse
    {
        $event = UserEvent::where('user_id', (int) $request->user()->id)->findOrFail((int) $id);
        if (!$event->dismissed_at) {
            $event->forceFill(['dismissed_at' => now()])->save();
        }

        return response()->json(['success' => true]);
    }

    public function readAllNotifications(Request $request): JsonResponse
    {
        UserEvent::where('user_id', (int) $request->user()->id)
            ->whereNull('dismissed_at')
            ->update(['dismissed_at' => now()]);

        return response()->json(['success' => true]);
    }

    private function notificationRow(UserEvent $event): array
    {
        $data = is_array($event->data) ? $event->data : [];
        $type = (string) ($data['type'] ?? $event->type ?? 'system');
        $title = (string) ($data['title'] ?? $data['subject'] ?? $event->title ?? 'Уведомление');
        $text = (string) ($data['text'] ?? $data['message'] ?? $event->message ?? '');

        return [
            'id' => $event->id,
            'title' => $title,
            'text' => $text,
            'message' => $text,
            'type' => $type,
            'category' => $type,
            'created_at' => $event->created_at,
            'read' => (bool) $event->dismissed_at,
            'is_read' => (bool) $event->dismissed_at,
            'icon' => (string) ($data['icon'] ?? '✦'),
            'data' => $data,
        ];
    }
}
