<?php

declare(strict_types=1);

namespace App\Console\Commands\Tests;

use App\Models\Likes;
use App\Models\Story;
use App\Models\User;
use App\Services\LikeActionRateLimiter;
use App\Services\SuspiciousAccountService;
use Illuminate\Console\Command;

class TestLikeRateLimit extends Command
{
    protected $signature = 'test:rate-limit:likes {user_id} {--count=51} {--reset}';
    protected $description = 'Проверить лимит лайков с подбором сторис без лайка пользователя';

    public function handle(LikeActionRateLimiter $limiter, SuspiciousAccountService $suspicious): int
    {
        $user = User::findOrFail((int) $this->argument('user_id'));

        if ($this->option('reset')) {
            $limiter->clear($user);
            $user->clearSuspiciousStatus();
        }

        $storyIds = Story::withoutGlobalScopes()
            ->whereNotIn('id', Likes::query()->where('user_id', $user->id)->whereNotNull('story_id')->select('story_id'))
            ->limit((int) $this->option('count'))
            ->pluck('id');

        if ($storyIds->isEmpty()) {
            $this->error('Не найдены сторис без лайка этого пользователя.');
            return self::FAILURE;
        }

        foreach ($storyIds as $index => $storyId) {
            $attempt = $index + 1;
            $reason = $limiter->hit($user);
            $this->line("Запрос {$attempt}, story_id={$storyId}: ".($reason ?? 'allowed'));
            if ($reason !== null) {
                $suspicious->markSuspicious($user);
                break;
            }
        }

        if ($storyIds->count() < (int) $this->option('count')) {
            $this->warn('Найдено только '.$storyIds->count().' подходящих сторис.');
        }

        $this->info('Итог: is_suspicious='.($user->fresh()->is_suspicious ? 'true' : 'false'));
        return self::SUCCESS;
    }
}
