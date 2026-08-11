<?php

namespace App\Observers\Games;

use App\Helpers\AppHelper;
use App\Jobs\Moderation\CheckImage;
use App\Jobs\Moderation\CheckText;
use App\Jobs\NotifyAllChannels;
use App\Jobs\SendTGCampaignModeration;
use App\Jobs\User\UpdateUsersBalance;
use App\Models\Campaign;
use App\Models\Games\Game;
use App\Models\Games\GameSession;
use Illuminate\Support\Facades\Log;

class GameSessionObserver
{
    public function creating(GameSession $session)
    {

    }

    public function created(GameSession $session)
    {

    }

    public function updating(GameSession $session)
    {

    }

    public function updated(GameSession $session)
    {
        if ($session->isDirty('status') && $session->status == 'win') {
            $game = Game::where('type', $session->game)->first();
            if ($game) {
                $prize = $game->settings['prize'] ?? 0;
                if ($session->game == 'wheel') {
                    $prize = $session->prize;
                }
                Log::info('$prize '. $prize);
                $session->prize = $prize;
                $session->saveQuietly();
                UpdateUsersBalance::dispatchSync($session->user_id, $session->prize / 50, [], [], 'Выигрыш в игре');
            }
        }
    }

    public function deleted(GameSession $session)
    {
        //
    }

    public function restored(GameSession $session)
    {
        //
    }

    public function forceDeleted(GameSession $session)
    {
        //
    }
}
