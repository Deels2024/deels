<?php

namespace App\Jobs;

use App\Helpers\AppHelper;
use App\Models\Challenge;
use App\Models\Story;
use App\Services\Contests\ContestNotificationService;
use App\Services\Contests\ChallengeWinnerSelectionService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class FinishChallenge implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    private $challenge;

    public function __construct($challenge)
    {
        $this->challenge = $challenge;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $challenge = $this->challenge;
        $challenge = Challenge::find($this->challenge->id);
        $telegram = new AppHelper();
        if(!$challenge->finished) {
            $winner_selection = $challenge->winner_selection ?: 'likes';
            $hasParticipants = DB::table('contest_participations')
                ->where([
                    'contest_type' => 'challenge',
                    'contest_id' => $challenge->id,
                    'status' => 'active',
                ])
                ->exists() || Story::withoutGlobalScopes()
                    ->where('challenge_id', $challenge->id)
                    ->notMainStory()
                    ->whereNull('withdrawn_at')
                    ->exists();

            if (!$hasParticipants) {
                $result = app(ChallengeWinnerSelectionService::class)->finishByLikes($challenge);
                $challenge->winners()->detach();
                $challenge->finished = true;
                $challenge->finished_at = Carbon::now();
                $challenge->completion_status = 'skipped';
                $challenge->winner_selection_status = 'auto';
                $challenge->winner_selected_at = Carbon::now();
                $challenge->winner_decided_by_user_id = null;
                $challenge->saveQuietly();

                app(ContestNotificationService::class)->results(
                    $challenge,
                    'challenge',
                    $result['stories'],
                    [],
                    $result['prize']
                );
                return null;
            }

            if($winner_selection === 'creator') {
                $challenge->finished = true;
                $challenge->finished_at = Carbon::now();
                $challenge->completion_status = 'completed';
                $challenge->winner_selection_status = 'pending';
                $challenge->winner_selection_deadline = Carbon::now()->addDays(3);
                $challenge->saveQuietly();
                $telegram->telegram_message('Challenge ID' . $challenge->id . ' ожидает ручного выбора победителя до ' . $challenge->winner_selection_deadline->format('d.m.Y H:i'));
                ResolveChallengeCreatorWinners::dispatch($challenge->id)->delay($challenge->winner_selection_deadline);
                return null;
            }

            $check_challenge = Challenge::find($challenge->id);

            if($check_challenge->finished) {
                $telegram->telegram_message('Challenge ID' . $challenge->id . ' already finished');
                return null;
            }

            $result = app(ChallengeWinnerSelectionService::class)->finishByLikes($challenge);
            $telegram->telegram_message('Challenge ID' . $challenge->id . ' users for win: ' . count($result['winner_story_ids']));

            app(ContestNotificationService::class)->results(
                $challenge,
                'challenge',
                $result['stories'],
                $result['winner_story_ids'],
                $result['prize']
            );


//        dd($prize,$winners,$challenge_results);

            $telegram->chat_notify($challenge->user,'Челлендж "' . $challenge->title . '" завершен!',null);

            FireBaseEvent::dispatch( $challenge->user_id, 'Срок действия челленджа завершен,время узнать победителя!', $challenge->id, 'challenge');

            $challenge->finished = true;
            $challenge->finished_at = Carbon::now();
            $challenge->completion_status = 'completed';
            $challenge->winner_selection_status = 'auto';
            $challenge->winner_selected_at = Carbon::now();
            $challenge->winner_decided_by_user_id = null;
            $challenge->saveQuietly();
        }
    }

    public function failed(\Throwable $e)
    {
        $challenge = $this->challenge;
        $challenge->finished = true;
        $challenge->finished_at = Carbon::now();
        $challenge->saveQuietly();
        $telegram = new AppHelper();
        $telegram->telegram_message('!!!! Challenge ID' . $challenge->id . ' завершился с ошибкой!');
    }
}
