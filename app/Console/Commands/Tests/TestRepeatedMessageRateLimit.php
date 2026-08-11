<?php

declare(strict_types=1);

namespace App\Console\Commands\Tests;

use App\Models\User;
use App\Services\MessageActionRateLimiter;
use App\Services\SuspiciousAccountService;
use Illuminate\Console\Command;

class TestRepeatedMessageRateLimit extends Command
{
    protected $signature = 'test:rate-limit:repeated-messages {user_id} {receiver_id} {--count=6} {--message=Тестовое одинаковое сообщение} {--reset}';
    protected $description = 'Проверить лимит одинаковых сообщений одного пользователя';

    public function handle(MessageActionRateLimiter $limiter, SuspiciousAccountService $suspicious): int
    {
        $user = User::findOrFail((int) $this->argument('user_id'));
        User::findOrFail((int) $this->argument('receiver_id'));
        $message = (string) $this->option('message');

        if ($this->option('reset')) {
            $limiter->clear($user, $message);
            $user->clearSuspiciousStatus();
        }

        for ($i = 1; $i <= (int) $this->option('count'); $i++) {
            $reason = $limiter->hit($user, $message);
            $this->line("Запрос {$i}: ".($reason ?? 'allowed'));
            if ($reason !== null) {
                $suspicious->markSuspicious($user);
                break;
            }
        }

        $this->info('Итог: is_suspicious='.($user->fresh()->is_suspicious ? 'true' : 'false'));
        return self::SUCCESS;
    }
}
