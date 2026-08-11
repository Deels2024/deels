<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Contests;

use App\Jobs\NotifyAllChannels;
use App\Models\Battle;
use App\Models\Challenge;
use App\Models\Story;
use App\Models\User;
use App\Services\Contests\ContestNotificationService;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Support\CreatesCharacterizationSchema;
use Tests\TestCase;

class ContestNotificationServiceTest extends TestCase
{
    use CreatesCharacterizationSchema;

    private ContestNotificationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createCharacterizationSchema();
        Bus::fake();
        $this->service = new ContestNotificationService;
    }

    public function test_challenge_invites_are_sent_on_first_publication_only_once(): void
    {
        $challenge = Challenge::withoutEvents(fn () => Challenge::create([
            'user_id' => $this->user('Автор')->id,
            'title' => 'Новый старт',
            'invite_user_ids' => [$this->user('Гость')->id],
        ]));

        $this->service->challengePublished($challenge);
        $this->service->challengePublished($challenge);

        Bus::assertDispatchedTimes(NotifyAllChannels::class, 2);
        self::assertSame(1, DB::table('contest_notification_deliveries')->where('kind', 'invite')->count());
        self::assertSame(1, DB::table('contest_notification_deliveries')->where('kind', 'update:2')->count());
    }

    public function test_new_challenge_invitee_is_notified_after_republication(): void
    {
        $owner = $this->user('Автор');
        $first = $this->user('Первый');
        $second = $this->user('Второй');
        $challenge = Challenge::withoutEvents(fn () => Challenge::create([
            'user_id' => $owner->id,
            'title' => 'Челлендж',
            'invite_user_ids' => [$first->id],
        ]));

        $this->service->challengePublished($challenge);
        $challenge->forceFill(['invite_user_ids' => [$first->id, $second->id]])->saveQuietly();
        $this->service->challengePublished($challenge->fresh());

        self::assertSame(2, DB::table('contest_notification_deliveries')->where('kind', 'invite')->count());
    }

    public function test_battle_call_precedes_invites_until_called_user_accepts(): void
    {
        $author = $this->user('Автор');
        $called = $this->user('Соперник');
        $guest = $this->user('Зритель');
        $battle = Battle::withoutEvents(fn () => Battle::create([
            'user_id' => $author->id,
            'called_user_id' => $called->id,
            'title' => 'Главный батл',
            'invite_user_ids' => [$guest->id],
        ]));

        $this->service->battleModerated($battle);
        self::assertSame(1, DB::table('contest_notification_deliveries')->where('kind', 'call')->count());
        self::assertSame(0, DB::table('contest_notification_deliveries')->where('kind', 'invite')->count());

        $this->service->battleAccepted($battle);
        self::assertSame(1, DB::table('contest_notification_deliveries')->where('kind', 'invite')->count());
    }

    public function test_winner_receives_victory_and_prize_message(): void
    {
        $owner = $this->user('Автор');
        $winner = $this->user('Победитель');
        $challenge = Challenge::withoutEvents(fn () => Challenge::create([
            'user_id' => $owner->id,
            'title' => 'Большой старт',
            'active' => true,
        ]));
        $story = Story::withoutEvents(fn () => Story::create([
            'user_id' => $winner->id,
            'challenge_id' => $challenge->id,
            'active' => true,
        ]));

        $this->service->results($challenge, 'challenge', collect([$story]), [$story->id], 100);

        Bus::assertDispatched(NotifyAllChannels::class, function ($job) use ($winner): bool {
            return $this->jobValue($job, 'user_id') === $winner->id
                && str_contains($this->jobValue($job, 'message'), 'Потрясающая работа!')
                && str_contains($this->jobValue($job, 'message'), 'Ваш приз уже начислен!');
        });
        $event = DB::table('user_events')->where('user_id', $winner->id)->first();
        $data = json_decode($event->data, true);
        self::assertSame('challenge_win', $event->result);
        self::assertSame(100, $data['reward_amount']);
        self::assertSame('fireworks', $data['presentation']['background']);
        self::assertTrue($data['presentation']['closable']);
        self::assertTrue(now()->addDays(13)->lt($event->expires_at));
    }

    public function test_winner_event_is_created_when_notification_was_already_delivered(): void
    {
        $owner = $this->user('Автор');
        $winner = $this->user('Победитель');
        $battle = Battle::withoutEvents(fn () => Battle::create([
            'user_id' => $owner->id,
            'called_user_id' => $winner->id,
            'title' => 'Повторный результат',
            'active' => true,
        ]));
        $story = Story::withoutEvents(fn () => Story::create([
            'user_id' => $winner->id,
            'battle_id' => $battle->id,
            'active' => true,
        ]));
        DB::table('contest_notification_deliveries')->insert([
            'contest_type' => 'battle',
            'contest_id' => $battle->id,
            'user_id' => $winner->id,
            'kind' => 'result',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->service->results($battle, 'battle', collect([$story]), [$story->id], 0);

        self::assertDatabaseHas('user_events', [
            'user_id' => $winner->id,
            'source_type' => 'battle',
            'source_id' => $battle->id,
            'result' => 'battle_win',
        ]);
    }

    public function test_battle_draw_notifies_both_participants_with_opponent_names(): void
    {
        $first = $this->user('Первый');
        $second = $this->user('Второй');
        $battle = Battle::withoutEvents(fn () => Battle::create([
            'user_id' => $first->id,
            'called_user_id' => $second->id,
            'title' => 'Равный бой',
            'active' => true,
        ]));
        $stories = collect([
            Story::withoutEvents(fn () => Story::create([
                'user_id' => $first->id,
                'battle_id' => $battle->id,
                'active' => true,
            ])),
            Story::withoutEvents(fn () => Story::create([
                'user_id' => $second->id,
                'battle_id' => $battle->id,
                'active' => true,
            ])),
        ]);

        $this->service->results($battle, 'battle', $stories, $stories->pluck('id')->all(), 0);

        Bus::assertDispatchedTimes(NotifyAllChannels::class, 2);
        Bus::assertDispatched(NotifyAllChannels::class, function ($job) use ($first, $second): bool {
            return $this->jobValue($job, 'user_id') === $first->id
                && str_contains($this->jobValue($job, 'message'), $second->name);
        });
        $event = DB::table('user_events')->where('user_id', $first->id)->first();
        $data = json_decode($event->data, true);
        self::assertSame('battle_draw', $event->result);
        self::assertSame($second->id, $data['opponent']['id']);
        self::assertStringContainsString(route('user.profile', $second->id), $data['message']);
    }

    public function test_loser_message_contains_finished_and_random_challenge_links(): void
    {
        $owner = $this->user('Автор');
        $loser = $this->user('Участник');
        $finished = Challenge::withoutEvents(fn () => Challenge::create([
            'user_id' => $owner->id,
            'title' => 'Прошедший',
            'active' => true,
            'finished' => true,
        ]));
        $available = Challenge::withoutEvents(fn () => Challenge::create([
            'user_id' => $owner->id,
            'title' => 'Доступный',
            'active' => true,
            'finished' => false,
            'participants_count' => 0,
        ]));
        $story = Story::withoutEvents(fn () => Story::create([
            'user_id' => $loser->id,
            'challenge_id' => $finished->id,
            'active' => true,
        ]));

        $this->service->results($finished, 'challenge', collect([$story]), [], 0);

        Bus::assertDispatched(NotifyAllChannels::class, function ($job) use ($finished, $available): bool {
            $message = $this->jobValue($job, 'message');

            return str_contains($message, route('challenge_page', $finished->id))
                && str_contains($message, route('challenge_page', $available->id))
                && str_contains($message, '>другом челлендже</a>');
        });
    }

    private function user(string $name): User
    {
        return User::create([
            'name' => $name,
            'email' => strtolower($name).'@example.test',
            'password' => 'secret',
        ]);
    }

    private function jobValue(NotifyAllChannels $job, string $property)
    {
        $reflection = new \ReflectionProperty($job, $property);
        $reflection->setAccessible(true);

        return $reflection->getValue($job);
    }
}
