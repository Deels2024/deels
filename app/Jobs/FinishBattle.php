<?php

namespace App\Jobs;

use App\Helpers\AppHelper;
use App\Models\Battle;
use App\Models\Challenge;
use App\Models\Story;
use App\Services\Contests\ContestNotificationService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class FinishBattle implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    private $battle;

    public function __construct($battle)
    {
        $this->battle = $battle;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $battle = $this->battle;
        $battle = Battle::find($this->battle->id);
        $telegram = new AppHelper();
        if(!$battle->finished) {
            $winner_selection = $battle->winner_selection ?: 'likes';
            $winners = [];
            $amount = $battle->amount;

            if($winner_selection !== 'likes') {
                $battle->finished = true;
                $battle->finished_at = Carbon::now();
                $battle->completion_status = 'completed';
                $battle->saveQuietly();
                $telegram->telegram_message('Battle ID' . $battle->id . ' завершен без автоопределения победителя');
                $payments_wallet = $battle->user->getWallet('payments');
                $balance = intval($payments_wallet->balance ?? 0);
                $payments_wallet->deposit(intval($amount), ['get' => 'coins', 'balance_before' => $balance, 'description' => 'Возврат за батл "' . $battle->title . '"']);
                $telegram = new AppHelper();
                $telegram->telegram_message('Возврат за батл ID'.$battle->id.' "' . $battle->title . '"');
                return null;
            }

            $stories = Story::withoutGlobalScope('banned')
                ->withCount('likes')
                ->active()
                ->orderBy('likes_count', 'desc')
                ->where('battle_id', $battle->id)
                ->where(function ($query): void {
                    $query->where('is_main_story', false)
                        ->orWhereNull('is_main_story');
                })
                ->where('frozen', false)
                ->where('banned', false)
                ->get();

            $participationStatuses = DB::table('contest_participations')
                ->where('contest_type', 'battle')
                ->where('contest_id', $battle->id)
                ->pluck('status', 'user_id');
            $activeOpponentIds = $participationStatuses
                ->filter(fn ($status, $userId) => $status === 'active' && (int) $userId !== (int) $battle->user_id)
                ->keys()
                ->map(fn ($userId) => (int) $userId);
            $legacyOpponentIds = $stories->pluck('user_id')
                ->map(fn ($userId) => (int) $userId)
                ->filter(fn ($userId) => $userId !== (int) $battle->user_id)
                ->filter(fn ($userId) => !isset($participationStatuses[$userId]) || $participationStatuses[$userId] === 'active');
            $opponentIds = $activeOpponentIds->merge($legacyOpponentIds)->unique()->values();

            $participantIds = collect([(int) $battle->user_id])->merge($opponentIds)->unique()->values();
            $scores = $participantIds->mapWithKeys(function (int $userId) use ($stories): array {
                return [$userId => (int) $stories->where('user_id', $userId)->sum('likes_count')];
            });
            $maxScore = (int) ($scores->max() ?? 0);
            $topUserIds = $scores->filter(fn ($score) => (int) $score === $maxScore)->keys()->map(fn ($id) => (int) $id);

            if ($opponentIds->isEmpty()) {
                $battle->completion_status = 'skipped';
            } elseif ($topUserIds->count() !== 1) {
                $battle->completion_status = 'draw';
            } else {
                $battle->completion_status = 'completed';
            }

            $winnerUserId = $battle->completion_status === 'completed' ? $topUserIds->first() : null;
            $best_story = $winnerUserId
                ? $stories->where('user_id', $winnerUserId)->sortByDesc('likes_count')->first()
                : null;
            $hasWinningResult = $best_story && $maxScore > 0;

            if (!$hasWinningResult) {
                $battle->winners()->detach();
            }

            $similarStories = [];
            if($hasWinningResult) {
                $similarStories = collect([$best_story]);
            }

            if (!empty($similarStories) && count($similarStories)) {
                foreach ($similarStories as $similarStory) {
                    $winners[] = $similarStory;
                }
            }

            if(count($winners)) {
                $prize = intval(ceil($amount / count($winners)));
            } else {
                $prize = intval($amount);
            }

            $battle_results = 0;

            if($hasWinningResult) {
                $battle_results = $best_story->likes_count;
            }


            $check_battle = Battle::find($battle->id);

            if($check_battle->finished) {
                $telegram->telegram_message('Battle ID' . $battle->id . ' already finished');
                return null;
            }

            $telegram->telegram_message('Battle ID' . $battle->id . ' users for win: ' . count($winners));

            $winners_ids = [];
            $current_winners = $battle->winners()->pluck('user_id')->toArray();

            if(!$battle->finished_at && $battle_results > 0) {
                if(count($winners)) {
                    foreach ($winners as $winner) {
                        if(!in_array($winner->user->id, $current_winners)) {
                            if ($prize > 0) {
                                $winner->user->deposit($prize, ['get' => 'coins', 'description' => 'Победа в батле "' . $battle->title . '"']);
                            }
                            $battle->winners()->save($winner->user);
                            $telegram->telegram_message('Battle ID' . $battle->id . ' winner: ' . $winner->user->fullname . ' (ID ' . $winner->user->id . ') / Prize: ' . $prize . ' coins');
                        }
                        $winners_ids[] = $winner->id;
                    }
                    $battle->finished = true;
                } else {
                    $payments_wallet = $battle->user->getWallet('payments');
                    $balance = intval($payments_wallet->balance ?? 0);
                    $payments_wallet->deposit(intval($amount), ['get' => 'coins', 'balance_before' => $balance, 'description' => 'Возврат за батл "' . $battle->title . '"']);
                    $telegram->telegram_message('Battle ID' . $battle->id . ' Возврат за батл');
                    $battle->finished = true;

                    if($battle->cost && $battle->cost > 0) {
                        foreach ($battle->stories as $participant_story) {
                            $participant_story_payments_wallet = $participant_story->user->getWallet('payments');
                            $participant_story_balance = intval($participant_story_payments_wallet->balance ?? 0);
                            $participant_story_payments_wallet->deposit(intval($battle->cost), ['get' => 'coins', 'balance_before' => $participant_story_balance, 'description' => 'Возврат за участие в батле "' . $battle->title . '"']);
                        }
                    }
                }

            } else {
                $payments_wallet = $battle->user->getWallet('payments');
                $balance = intval($payments_wallet->balance ?? 0);
                $payments_wallet->deposit(intval($amount), ['get' => 'coins', 'balance_before' => $balance, 'description' => 'Возврат за батл "' . $battle->title . '"']);
                $battle->finished = true;
                $telegram = new AppHelper();
                $telegram->telegram_message('Возврат за батл ID'.$battle->id.' "' . $battle->title . '"');

                if($battle->cost && $battle->cost > 0) {
                    foreach ($battle->stories as $participant_story) {
                        $participant_story_payments_wallet = $participant_story->user->getWallet('payments');
                        $participant_story_balance = intval($participant_story_payments_wallet->balance ?? 0);
                        $participant_story_payments_wallet->deposit(intval($battle->cost), ['get' => 'coins', 'balance_before' => $participant_story_balance, 'description' => 'Возврат за участие в батле "' . $battle->title . '"']);
                    }
                }
            }

            app(ContestNotificationService::class)->results(
                $battle,
                'battle',
                $stories,
                $winners_ids,
                $prize
            );


//        dd($prize,$winners,$battle_results);

            $telegram->chat_notify($battle->user,'батл "' . $battle->title . '" завершен!',null);

            FireBaseEvent::dispatch( $battle->user_id, 'Срок действия батла завершен,время узнать победителя!', $battle->id, 'battle');

            $battle->finished = true;
            $battle->finished_at = Carbon::now();
            $battle->saveQuietly();
        }
    }

    public function failed(\Throwable $e)
    {
        $battle = $this->battle;
        $battle->finished = true;
        $battle->finished_at = Carbon::now();
        $battle->saveQuietly();
        $telegram = new AppHelper();
        $telegram->telegram_message('!!!! Battle ID' . $battle->id . ' завершился с ошибкой!');
    }
}
