<?php

namespace App\Console\Commands\Challenges;

use App\Jobs\ResolveChallengeCreatorWinners;
use App\Models\Challenge;
use Illuminate\Console\Command;

class ResolvePendingWinnerSelections extends Command
{
    protected $signature = 'challenges:winners:resolve-pending';

    protected $description = 'Resolve challenge winners after creator selection deadline';

    public function handle(): int
    {
        Challenge::query()
            ->where('winner_selection', 'creator')
            ->where('winner_selection_status', 'pending')
            ->whereNotNull('winner_selection_deadline')
            ->where('winner_selection_deadline', '<=', now())
            ->orderBy('id')
            ->each(static function (Challenge $challenge): void {
                ResolveChallengeCreatorWinners::dispatch($challenge->id);
            });

        return self::SUCCESS;
    }
}
