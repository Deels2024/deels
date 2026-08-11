<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Campaign;
use App\Models\User;
use Illuminate\Console\Command;

class CheckCampaignAuthorsActivity extends Command
{
    protected $signature = 'campaigns:authors:activity-check';

    protected $description = 'Check campaign authors activity and decrease campaign health after inactivity';

    public function handle(): int
    {
        $checkedAt = now();
        $checkBefore = $checkedAt->copy()->subDays(7);
        $checked = 0;
        $inactive = 0;

        User::query()
            ->where(function ($query) use ($checkBefore): void {
                $query
                    ->whereNull('checked_at')
                    ->orWhere('checked_at', '<=', $checkBefore);
            })
            ->whereHas('my_campaigns', function ($query): void {
                $query->whereNotIn('status', [Campaign::STATUS_FINISHED, Campaign::STATUS_ARCHIVED]);
            })
            ->chunkById(100, function ($users) use ($checkedAt, &$checked, &$inactive): void {
                foreach ($users as $user) {
                    if (!$user->isActiveAuthor()) {
                        Campaign::healthDown(3, $user->id);
                        $inactive++;
                    }

                    $user->forceFill(['checked_at' => $checkedAt])->save();
                    $checked++;
                }
            });

        $this->info("Checked {$checked} users, inactive {$inactive} users.");

        return self::SUCCESS;
    }
}
