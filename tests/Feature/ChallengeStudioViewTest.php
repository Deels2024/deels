<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Challenge;
use App\Services\Contests\ContestInvitationService;
use Illuminate\Support\Facades\Auth;
use Mockery;
use Tests\TestCase;

class ChallengeStudioViewTest extends TestCase
{
    public function test_guest_gets_login_instead_of_a_misleading_closed_recruitment_button(): void
    {
        $html = $this->overview(null);

        $this->assertStringContainsString('href="'.route('login').'"', $html);
        $this->assertStringContainsString('Войти для участия', $html);
        $this->assertStringNotContainsString('Набор закрыт', $html);
        $this->assertStringNotContainsString('<form', $html);
    }

    /** @dataProvider participationActions */
    public function test_join_and_rejoin_keep_the_existing_post_routes(string $action): void
    {
        $html = $this->overview(7, ['participationState' => ['action' => $action]]);

        $this->assertStringContainsString('method="POST"', $html);
        $this->assertStringContainsString('action="'.route('contests.participation.'.$action, ['type' => 'challenge', 'id' => 31]).'"', $html);
        $this->assertStringContainsString('name="_token"', $html);
        $this->assertStringNotContainsString('Войти для участия', $html);
    }

    public static function participationActions(): array
    {
        return [['join'], ['rejoin']];
    }

    public function test_participant_sees_the_real_report_link_and_leave_confirmation(): void
    {
        $html = $this->overview(7, [
            'participationState' => ['action' => 'leave', 'participating' => true],
            'reportingState' => ['visible' => true, 'available' => true, 'checkin' => 'story', 'story_allowed' => true],
        ]);

        $this->assertStringContainsString('Ты участвуешь', $html);
        $this->assertStringContainsString('href="'.e(route('stories.create', ['challenge' => 31, 'online_report' => 1])).'"', $html);
        $this->assertStringContainsString('data-confirm-action="'.route('contests.participation.leave', ['type' => 'challenge', 'id' => 31]).'"', $html);
    }

    public function test_exhausted_story_limit_has_an_explanation_and_no_upload_link(): void
    {
        $html = $this->overview(7, [
            'participationState' => ['action' => 'leave', 'participating' => true],
            'reportingState' => ['visible' => true, 'available' => true, 'checkin' => 'story', 'story_allowed' => false],
        ]);

        $this->assertStringContainsString('Лимит видео за текущий период исчерпан', $html);
        $this->assertStringContainsString('aria-disabled="true"', $html);
        $this->assertStringNotContainsString('online_report', $html);
    }

    public function test_finished_challenge_has_no_join_or_login_call_to_action(): void
    {
        $html = $this->overview(null, ['participationState' => ['action' => 'disabled', 'label' => 'Завершен']], ['finished' => true]);

        $this->assertStringNotContainsString('Войти для участия', $html);
        $this->assertStringNotContainsString('<form', $html);
        $this->assertStringContainsString('disabled', $html);
        $this->assertStringContainsString('Завершен', $html);
    }

    public function test_owner_tools_and_invitation_endpoints_are_preserved(): void
    {
        $html = $this->overview(50, ['isOwner' => true], [], true);

        $this->assertStringContainsString('href="'.route('challenges.edit', ['id' => 31]).'"', $html);
        $this->assertStringContainsString('data-users-url="'.route('contests.invites.users', ['type' => 'challenge', 'id' => 31]).'"', $html);
        $this->assertStringContainsString('data-store-url="'.route('contests.invites.store', ['type' => 'challenge', 'id' => 31]).'"', $html);
    }

    public function test_paid_entry_and_user_text_are_rendered_accurately(): void
    {
        $html = $this->overview(null, [], ['cost' => 0.5, 'title' => '<script>alert(1)</script>']);

        $this->assertStringContainsString('Стоимость участия', $html);
        $this->assertStringContainsString('0,5 DEELS', $html);
        $this->assertStringNotContainsString('Бесплатно', $html);
        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }

    private function overview(?int $viewer, array $overrides = [], array $attributes = [], bool $invites = false): string
    {
        Auth::shouldReceive('id')->andReturn($viewer);
        $invitationService = $this->createMock(ContestInvitationService::class);
        $invitationService->method('permissions')->willReturn(['allowed' => $invites, 'friends_only' => false]);
        $invitationService->method('ids')->willReturn([]);
        $this->instance(ContestInvitationService::class, $invitationService);
        $props = array_merge([
            'id' => 31, 'title' => 'Test challenge', 'description' => 'A real task',
            'type' => 'image', 'thumbnail' => '/test.jpg', 'path' => '/test.jpg',
            'video_preview' => null, 'finished' => false, 'declined' => false,
            'user' => (object) ['id' => 50, 'name' => 'author', 'fullname' => 'Author', 'avatar_url' => '/avatar.png'],
            'status_title' => 'Длится', 'rhythm' => 'once', 'checkin' => 'story',
            'winner_selection' => 'likes', 'reward_amount' => 100, 'amount' => 0, 'cost' => 0,
            'invite_user_ids' => [],
        ], $attributes);
        $contest = Mockery::mock(Challenge::class)->makePartial();
        $contest->shouldReceive('getAttribute')->andReturnUsing(static fn ($key) => $props[$key] ?? null);

        $data = array_replace_recursive([
            'contest' => $contest, 'deelsStudio' => true, 'mainStory' => null, 'isBattle' => false,
            'contestType' => 'challenge', 'contestTitle' => 'челлендж', 'routeParam' => 'challenge',
            'ownerId' => $viewer, 'isOwner' => false, 'hideClosedBattleAction' => false,
            'participationState' => ['action' => 'disabled', 'label' => 'Набор закрыт', 'participating' => false, 'called' => false],
            'reportingState' => ['visible' => false], 'leaveConfirmation' => 'Подтвердить выход?',
            'participantsCount' => 7, 'participantLimit' => 0, 'remainingPlaces' => 0,
            'recruitmentOpen' => true, 'periodStart' => null, 'periodFinish' => null,
            'rhythmLabels' => ['once' => 'Один раз'], 'checkinLabels' => ['story' => 'Сторис'],
            'winnerSelectionLabels' => ['likes' => 'По лайкам'], 'topWinners' => collect(),
            'editRoute' => 'challenges.edit',
        ], $overrides);

        return view('challenges.partials.contest_overview', $data)->render();
    }
}
