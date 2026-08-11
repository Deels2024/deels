<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Battle;
use App\Models\Challenge;
use App\Services\Contests\ContestParticipationService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ContestParticipationController extends Controller
{
    public function leave(
        string $type,
        int $id,
        ContestParticipationService $participation,
        Request $request
    ): JsonResponse|RedirectResponse {
        $contest = $this->contest($type, $id);
        $participation->leave($contest, $type, (int) auth()->id());

        return $this->response($request, 'Участие прекращено', 'left');
    }

    public function rejoin(
        string $type,
        int $id,
        ContestParticipationService $participation,
        Request $request
    ): JsonResponse|RedirectResponse {
        $contest = $this->contest($type, $id);
        $participation->rejoin($contest, $type, (int) auth()->id());

        return $this->response($request, 'Участие возобновлено', 'active');
    }

    public function join(
        string $type,
        int $id,
        ContestParticipationService $participation,
        Request $request
    ): JsonResponse|RedirectResponse {
        $contest = $this->contest($type, $id);
        $participation->join($contest, $type, (int) auth()->id());

        return $this->response($request, 'Вы участвуете', 'active');
    }

    public function accept(int $id, ContestParticipationService $participation, Request $request): JsonResponse|RedirectResponse
    {
        $battle = Battle::findOrFail($id);
        $participation->accept($battle, (int) auth()->id());

        return $this->response($request, 'Вызов принят', 'active');
    }

    public function decline(int $id, ContestParticipationService $participation, Request $request): JsonResponse|RedirectResponse
    {
        $battle = Battle::findOrFail($id);
        $participation->decline($battle, (int) auth()->id());

        return $this->response($request, 'Вызов отклонен', 'declined');
    }

    private function contest(string $type, int $id): Model
    {
        abort_unless(in_array($type, ['challenge', 'battle'], true), 404);
        $model = $type === 'battle' ? Battle::class : Challenge::class;

        return $model::findOrFail($id);
    }

    private function response(Request $request, string $message, string $status): JsonResponse|RedirectResponse
    {
        if ($request->is('api/*') || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'status' => $status,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }
}
