<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Stories;

use App\Services\Stories\StoryAdValidator;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;

class StoryAdValidatorTest extends TestCase
{
    private StoryAdValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new StoryAdValidator();
    }

    public function test_non_ad_story_is_valid_with_existing_ads_data(): void
    {
        $result = $this->validator->validate(new Request([
            'ads_data' => ['tracking' => 'legacy'],
        ]));

        self::assertTrue($result->valid);
        self::assertNull($result->error);
        self::assertSame(['tracking' => 'legacy'], $result->adsData);
    }

    public function test_ad_story_is_rejected_for_challenge(): void
    {
        $result = $this->validator->validate(new Request([
            'is_ad' => 1,
            'challenge_id' => 10,
            'ads_data' => [
                'advertiser' => 'ACME',
                'erid' => 'erid-1',
            ],
        ]));

        self::assertFalse($result->valid);
        self::assertSame(StoryAdValidator::CHALLENGE_AD_ERROR, $result->error);
    }

    public function test_ad_story_requires_advertiser(): void
    {
        $result = $this->validator->validate(new Request([
            'is_ad' => 1,
            'ads_data' => [
                'erid' => 'erid-1',
            ],
        ]));

        self::assertFalse($result->valid);
        self::assertSame(StoryAdValidator::ADVERTISER_REQUIRED_ERROR, $result->error);
    }

    public function test_ad_story_requires_erid_or_get_erid(): void
    {
        $result = $this->validator->validate(new Request([
            'is_ad' => 1,
            'ads_data' => [
                'advertiser' => 'ACME',
            ],
        ]));

        self::assertFalse($result->valid);
        self::assertSame(StoryAdValidator::ERID_REQUIRED_ERROR, $result->error);
    }

    public function test_get_erid_from_request_is_added_to_ads_data(): void
    {
        $result = $this->validator->validate(new Request([
            'is_ad' => 1,
            'get_erid' => 1,
            'ads_data' => [
                'advertiser' => 'ACME',
            ],
        ]));

        self::assertTrue($result->valid);
        self::assertSame([
            'advertiser' => 'ACME',
            'get_erid' => 1,
        ], $result->adsData);
    }
}
