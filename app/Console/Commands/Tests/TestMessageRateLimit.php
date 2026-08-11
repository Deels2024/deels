<?php

declare(strict_types=1);

namespace App\Console\Commands\Tests;

use App\Models\User;
use App\Services\MessageActionRateLimiter;
use App\Services\SuspiciousAccountService;
use Illuminate\Console\Command;

class TestMessageRateLimit extends Command
{
    protected $signature = 'test:rate-limit:messages {user_id} {receiver_id} {--count=151} {--reset}';
    protected $description = 'Проверить лимит уникальных сообщений одного пользователя за минуту';

    public function handle(MessageActionRateLimiter $limiter, SuspiciousAccountService $suspicious): int
    {
        $user = User::findOrFail((int) $this->argument('user_id'));
        User::findOrFail((int) $this->argument('receiver_id'));

        if ($this->option('reset')) {
            $limiter->clear($user);
            $user->clearSuspiciousStatus();
        }

        $reason = null;
        for ($i = 1; $i <= (int) $this->option('count'); $i++) {
            $reason = $limiter->hit($user, 'rate-limit-test-'.$i.'-'.microtime(true));
            if ($reason !== null) {
                $suspicious->markSuspicious($user);
                $this->error("Запрос {$i}: {$reason}; is_suspicious=true");
                break;
            }
        }

        $this->info('Итог: is_suspicious='.($user->fresh()->is_suspicious ? 'true' : 'false'));
        return self::SUCCESS;
    }
}
