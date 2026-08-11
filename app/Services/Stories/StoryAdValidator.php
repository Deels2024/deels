<?php

declare(strict_types=1);

namespace App\Services\Stories;

use Illuminate\Http\Request;

class StoryAdValidator
{
    public const CHALLENGE_AD_ERROR = 'Нельзя добавить в челлендж сторис с рекламой!';
    public const ADVERTISER_REQUIRED_ERROR = 'Вы не указали рекламодателя!';
    public const ERID_REQUIRED_ERROR = 'Вы не указали ерид!';

    public function validate(Request $request): StoryAdValidationResult
    {
        $adsData = $request->input('ads_data') ?? [];

        if ($request->input('get_erid')) {
            $adsData['get_erid'] = $request->input('get_erid');
        }

        if (!$request->input('is_ad')) {
            return StoryAdValidationResult::valid($adsData);
        }

        if ($request->input('challenge_id')) {
            return StoryAdValidationResult::invalid(self::CHALLENGE_AD_ERROR, $adsData);
        }

        if (!isset($adsData['advertiser'])) {
            return StoryAdValidationResult::invalid(self::ADVERTISER_REQUIRED_ERROR, $adsData);
        }

        if (!isset($adsData['erid']) && !isset($adsData['get_erid'])) {
            return StoryAdValidationResult::invalid(self::ERID_REQUIRED_ERROR, $adsData);
        }

        return StoryAdValidationResult::valid($adsData);
    }
}
