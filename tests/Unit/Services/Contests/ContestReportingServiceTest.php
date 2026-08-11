<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Contests;

use App\Models\Challenge;
use App\Models\ContestReport;
use App\Models\Story;
use App\Services\Contests\ContestParticipationService;
use App\Services\Contests\ContestReportingService;
use Carbon\Carbon;
use Tests\Feature\Support\CreatesCharacterizationSchema;
use Tests\TestCase;

class ContestReportingServiceTest extends TestCase
{
    use CreatesCharacterizationSchema;

    private ContestReportingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createCharacterizationSchema();
        $this->service = new ContestReportingService(new ContestParticipationService());
        Carbon::setTestNow('2026-07-27 12:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_value_is_updated_inside_shared_three_day_period(): void
    {
        $challenge = $this->challenge('value', 'three_days');
        $first = $this->service->submit($challenge, 'challenge', 20, 'value', 3.5);
        $second = $this->service->submit($challenge, 'challenge', 20, 'value', 8);

        self::assertFalse($first['updated']);
        self::assertTrue($second['updated']);
        self::assertSame(1, ContestReport::count());
        self::assertSame(8.0, ContestReport::first()->value);
        self::assertSame('2026-07-25 00:00:00', ContestReport::first()->period_started_at->format('Y-m-d H:i:s'));
    }

    public function test_deleted_story_remains_as_placeholder_and_is_removed_from_total(): void
    {
        $challenge = $this->challenge('story', 'daily');
        $story = Story::withoutEvents(fn () => Story::create([
            'user_id' => 20,
            'challenge_id' => $challenge->id,
            'active' => true,
        ]));
        $this->service->attachStory($challenge, 'challenge', 20, $story->id);

        self::assertSame(1, $this->service->state($challenge, 'challenge', 20)['total']);
        $story->delete();
        $state = $this->service->state($challenge, 'challenge', 20);

        self::assertNull($state['reports']->first()->story_id);
        self::assertSame(0, $state['total']);
    }

    public function test_reporting_is_visible_but_unavailable_before_start(): void
    {
        $challenge = $this->challenge('button', 'daily');
        $challenge->update([
            'started' => false,
            'date_from' => '2026-07-28 10:00:00',
            'date_to' => '2026-08-10 10:00:00',
        ]);

        $state = $this->service->state($challenge->fresh(), 'challenge', 20);

        self::assertTrue($state['visible']);
        self::assertFalse($state['available']);
    }

    public function test_reporting_is_available_by_dates_even_if_started_flag_is_stale(): void
    {
        $challenge = $this->challenge('value', 'daily');
        $challenge->update(['started' => false]);

        $state = $this->service->state($challenge->fresh(), 'challenge', 20);

        self::assertTrue($state['available']);
    }

    public function test_once_reporting_is_available_during_the_whole_finish_date(): void
    {
        Carbon::setTestNow('2026-08-03 15:00:00');
        $challenge = $this->challenge('story', 'once');
        $challenge->update([
            'date_from' => '2026-08-03 00:00:00',
            'date_to' => '2026-08-03 00:00:00',
        ]);

        $state = $this->service->state($challenge->fresh(), 'challenge', 20);

        self::assertTrue($state['available']);
        self::assertTrue($state['story_allowed']);
    }

    private function challenge(string $checkin, string $rhythm): Challenge
    {
        $challenge = Challenge::withoutEvents(fn () => Challenge::create([
            'user_id' => 10,
            'title' => 'Challenge',
            'active' => true,
            'started' => true,
            'participants_count' => 2,
            'checkin' => $checkin,
            'rhythm' => $rhythm,
            'date_from' => '2026-07-25 10:00:00',
            'date_to' => '2026-08-10 10:00:00',
        ]));
        Story::withoutEvents(fn () => Story::create([
            'user_id' => 20,
            'challenge_id' => $challenge->id,
            'active' => true,
        ]));

        return $challenge;
    }
}
