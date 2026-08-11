<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Battle;
use App\Models\Challenge;
use App\Services\Contests\ContestReportingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContestReportingController extends Controller
{
    public function store(
        Request $request,
        string $type,
        int $id,
        ContestReportingService $reporting
    ): JsonResponse {
        $contest = $type === 'battle' ? Battle::findOrFail($id) : Challenge::findOrFail($id);
        $kind = (string) $contest->checkin;
        $request->validate([
            'value' => $kind === 'value' ? ['required', 'numeric'] : ['nullable'],
        ]);
        $result = $reporting->submit(
            $contest,
            $type,
            (int) $request->user()->id,
            $kind,
            $kind === 'value' ? (float) $request->input('value') : null
        );
        $state = $reporting->state($contest, $type, (int) $request->user()->id);

        return response()->json([
            'success' => true,
            'updated' => $result['updated'],
            'message' => $result['updated'] ? 'Результат обновлен' : 'Результат отправлен',
            'report' => [
                'id' => $result['report']->id,
                'kind' => $result['report']->kind,
                'value' => $result['report']->value,
                'created_at' => $result['report']->created_at->format('d.m.Y H:i'),
            ],
            'total' => $state['total'],
        ]);
    }
}
