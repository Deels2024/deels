<?php

declare(strict_types=1);

namespace App\Console\Commands\System;

use App\Mail\InactiveAccountDeletedMail;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class DeleteUsersWithoutConfirmedActivity extends Command
{
    protected $signature = 'users:pending-action:delete';

    protected $description = 'Delete users who did not confirm activity within 30 days after need_action_at';

    public function handle(): int
    {
        $deleted = 0;
        $deadline = now()->subDays(30);

        User::query()
            ->whereNotNull('need_action_at')
            ->where('need_action_at', '<=', $deadline)
            ->chunkById(100, function ($users) use (&$deleted, $deadline): void {
                foreach ($users as $user) {
                    if ($user->need_action_at === null || $user->need_action_at->gt($deadline)) {
                        continue;
                    }

                    try {
                        $wasDeleted = DB::transaction(function () use ($user, $deadline): bool {
                            $pendingUser = User::query()
                                ->whereKey($user->id)
                                ->whereNotNull('need_action_at')
                                ->lockForUpdate()
                                ->first();

                            if (!$pendingUser || $pendingUser->need_action_at->gt($deadline)) {
                                return false;
                            }

                            Mail::to($pendingUser->email)->queue(new InactiveAccountDeletedMail());
                            $pendingUser->delete();

                            return true;
                        });

                        if ($wasDeleted) {
                            $deleted++;
                        }
                    } catch (\Throwable $exception) {
                        Log::error('Failed to delete user without confirmed activity.', [
                            'user_id' => $user->id,
                            'error' => $exception->getMessage(),
                        ]);
                    }
                }
            });

        $this->info('Deleted users: ' . $deleted);

        return self::SUCCESS;
    }
}
