<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Services\Home\HomePageDataService;
use Mockery;
use Tests\TestCase;

class HomePageApiContractTest extends TestCase
{
    public function test_home_v1_exposes_stable_sections_for_the_new_frontend(): void
    {
        $homePage = Mockery::mock(HomePageDataService::class);
        $homePage->shouldReceive('get')->once()->andReturn([
            'title' => HomePageDataService::TITLE,
            'description' => HomePageDataService::DESCRIPTION,
            'bank' => 13190964,
            'stats' => [
                'campaignsCount' => 12,
                'usersCount' => 100,
                'fundRaised' => 50000,
                'fundedCampaignsCount' => 4,
                'storiesCount' => 80,
                'storiesDonatedCount' => 20,
                'storiesCommentsCount' => 40,
                'storiesViewsCount' => 900,
                'challengesCount' => 10,
                'battlesCount' => 3,
                'participantsCount' => 25,
                'rewardsTotal' => 70000,
            ],
            'topChallenges' => collect(),
            'topBattles' => collect(),
            'topStories' => collect(),
            'donateStories' => collect(),
            'newStories' => collect(),
            'popularDirections' => collect(),
            'latestFundedCampaigns' => collect(),
            'newCampaigns' => collect(),
        ]);
        $this->app->instance(HomePageDataService::class, $homePage);

        $response = $this->getJson('/api/v1/home');

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('version', '1.0')
            ->assertJsonPath('data.bank.value', 13190964)
            ->assertJsonPath('data.bank.formatted', '13190964')
            ->assertJsonPath('data.filters.challenges.1.id', 'rewarded')
            ->assertJsonStructure([
                'data' => [
                    'meta' => ['title', 'description', 'section_order'],
                    'links' => ['challenges', 'battles', 'stories', 'campaigns'],
                    'bank' => ['value', 'formatted', 'refresh_url'],
                    'metrics',
                    'filters',
                    'sections' => [
                        'challenges',
                        'top_stories',
                        'donation_stories',
                        'new_stories',
                        'battles',
                        'directions',
                        'recently_funded_campaigns',
                        'new_campaigns',
                    ],
                ],
            ]);
    }
}
