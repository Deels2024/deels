<?php

namespace App\Jobs;

use App\Helpers\AppHelper;
use App\Models\Challenge;
use App\Services\Contests\ChallengeWinnerSelectionService;
use App\Services\Contests\ContestNotificationService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ResolveChallengeCreatorWinners implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private int $challengeId;

    public function __construct(int $challengeId)
    {
        $this->challengeId = $challengeId;
    }

    public function handle(ChallengeWinnerSelectionService $winnerService): void
    {
        $challenge = Challenge::find($this->challengeId);

        if (!$challenge
            || $challenge->winner_selection !== 'creator'
            || $challenge->winner_selection_status !== 'pending'
            || !$challenge->winner_selection_deadline
            || Carbon::parse($challenge->winner_selection_deadline)->isFuture()
        ) {
            return;
        }

        $result = $winnerService->finishPendingByFallback($challenge);

        app(ContestNotificationService::class)->results(
            $challenge,
            'challenge',
            $result['stories'],
            $result['winner_story_ids'],
            $result['prize']
        );

        (new AppHelper())->telegram_message('Challenge ID' . $challenge->id . ' winner selected automatically after creator deadline');
    }
}
