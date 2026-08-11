<?php

declare(strict_types=1);

namespace App\Jobs\Campaigns;

use App\Jobs\NotifyAllChannels;
use App\Models\Campaign;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ChangeCampaignHealth implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private const STATUS_SLEEPING = 4;
    private const STATUS_FINISHED = 5;
    private const STATUS_ARCHIVED = 6;
    private const HEALTH_MIN = 0;
    private const HEALTH_MAX = 10;
    private const HEALTH_LOW_NOTIFICATION = 'Упс! Твоя копилка оголодала без твоей активности и может уснуть. Скорее опубликуй сторис, задонать или пригласи друзей, чтобы не дать уснуть своей копилке на мечту!';
    private const HEALTH_EMPTY_NOTIFICATION = 'Упс! Твоя копилка оголодала без твоей активности и уснула. Скорее опубликуй сторис, задонать или пригласи друзей, чтобы разбудить копилку!';

    private int $userId;
    private int $value;

    public function __construct(int $userId, int $value)
    {
        $this->userId = $userId;
        $this->value = $value;
    }

    public function handle(): void
    {
        if ($this->userId === 0 || $this->value === 0) {
            return;
        }

        Campaign::query()
            ->where('user_id', $this->userId)
            ->whereNotIn('status', [self::STATUS_FINISHED, self::STATUS_ARCHIVED, self::STATUS_SLEEPING])
            ->where(function ($query): void {
                $query
                    ->whereDate('end_date', '<=', now()->toDateString())
                    ->orWhereNull('end_date');
            })
            ->get()
            ->each(function (Campaign $campaign): void {
                $oldHealth = (int) ($campaign->health ?? self::HEALTH_MAX);
                $newHealth = max(self::HEALTH_MIN, min(self::HEALTH_MAX, $oldHealth + $this->value));

                if ($newHealth === $oldHealth) {
                    return;
                }

                $campaign->health = $newHealth;

                if ($newHealth === self::HEALTH_MIN) {
                    $campaign->status = self::STATUS_SLEEPING;
                }

                $campaign->save();
                $this->notifyHealthChanged($campaign, $oldHealth, $newHealth);
            });
    }

    private function notifyHealthChanged(Campaign $campaign, int $oldHealth, int $newHealth): void
    {
        if ($oldHealth > 0 && $newHealth === 0) {
            NotifyAllChannels::dispatch($campaign->user_id, self::HEALTH_EMPTY_NOTIFICATION);
            return;
        }

        if ($oldHealth > 2 && $newHealth <= 2) {
            NotifyAllChannels::dispatch($campaign->user_id, self::HEALTH_LOW_NOTIFICATION);
        }
    }
}
