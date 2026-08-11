<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Contests;

use App\Models\Challenge;
use App\Models\Story;
use App\Services\Contests\ContestInvitationService;
use Tests\Feature\Support\CreatesCharacterizationSchema;
use Tests\TestCase;

class ContestInvitationServiceTest extends TestCase
{
    use CreatesCharacterizationSchema;

    private ContestInvitationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createCharacterizationSchema();
        $this->service = new ContestInvitationService();
    }

    public function test_author_can_invite_for_every_visibility(): void
    {
        foreach (['participants', 'friends', 'all'] as $visibility) {
            $permissions = $this->service->permissions($this->challenge($visibility), 'challenge', 10);

            self::assertTrue($permissions['allowed']);
            self::assertFalse($permissions['friends_only']);
        }
    }

    public function test_participant_can_invite_only_friends_for_friends_visibility(): void
    {
        $challenge = $this->challenge('friends');
        Story::withoutEvents(fn () => Story::create([
            'user_id' => 20,
            'challenge_id' => $challenge->id,
            'active' => true,
        ]));

        self::assertSame(
            ['allowed' => true, 'friends_only' => true],
            $this->service->permissions($challenge, 'challenge', 20)
        );
    }

    public function test_every_authenticated_viewer_can_invite_for_public_contest(): void
    {
        $permissions = $this->service->permissions($this->challenge('all'), 'challenge', 30);

        self::assertTrue($permissions['allowed']);
        self::assertFalse($permissions['friends_only']);
    }

    public function test_invited_viewer_cannot_invite_for_friends_visibility(): void
    {
        $challenge = $this->challenge('friends', ['invite_user_ids' => [30]]);

        self::assertFalse($this->service->permissions($challenge, 'challenge', 30)['allowed']);
    }

    private function challenge(string $visibility, array $attributes = []): Challenge
    {
        return Challenge::withoutEvents(fn () => Challenge::create(array_merge([
            'user_id' => 10,
            'title' => 'Challenge',
            'active' => true,
            'visibility' => $visibility,
        ], $attributes)));
    }
}
