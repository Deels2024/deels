<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Campaign;
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class CampaignShareThumbService
{
    private const DIRECTORY = 'uploads/campaigns/share-thumbs';

    public function generate(Campaign $campaign): ?string
    {
        try {
            $thumbnail = $this->publicPathFromUrl($campaign->feature_img_url()->thumbnail ?? null);

            if (!$thumbnail || !is_file($thumbnail)) {
                return null;
            }

            $shareUrl = $this->shareUrl($campaign);
            $relativePath = self::DIRECTORY.'/campaign_'.$campaign->id.'_'.time().'.png';
            $outputPath = public_path($relativePath);

            if (!is_dir(dirname($outputPath))) {
                mkdir(dirname($outputPath), 0755, true);
            }

            $image = Image::make($thumbnail)->orientate();
            $width = $image->width();
            $height = $image->height();

            $overlay = Image::canvas($width, $height, 'rgba(0, 0, 0, 0.6)');
            $image->insert($overlay, 'top-left');

            $qrPath = $this->makeQr($shareUrl);
            $qrSize = (int) round(min($width, $height) * 0.8);
            $qrSize = max($qrSize, 120);

            $qr = Image::make($qrPath)->resize($qrSize, $qrSize);
            $image->insert($qr, 'center');
            $image->save($outputPath, 90, 'png');

            @unlink($qrPath);
            $this->deleteOldThumb($campaign->share_thumb, $relativePath);

            return '/'.$relativePath;
        } catch (\Throwable $e) {
            Log::warning('Campaign share thumb generation failed', [
                'campaign_id' => $campaign->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function shareUrl(Campaign $campaign): string
    {
        $url = route('campaign_single', $campaign->slug);

        if (!empty($campaign->user?->referral_code)) {
            $url .= '?ref='.urlencode($campaign->user->referral_code);
        }

        return $url;
    }

    private function makeQr(string $url): string
    {
        $path = storage_path('app/tmp/campaign_share_qr_'.Str::random(16).'.png');

        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $options = new QROptions([
            'outputType' => QRCode::OUTPUT_IMAGE_PNG,
            'outputBase64' => false,
            'scale' => 10,
            'drawLightModules' => false,
            'imageTransparent' => true,
            'bgColor' => [0, 0, 0],
            'transparencyColor' => [0, 0, 0],
            'moduleValues' => [
                QRMatrix::M_DARKMODULE => [255, 255, 255],
                QRMatrix::M_DATA_DARK => [255, 255, 255],
                QRMatrix::M_FINDER_DARK => [255, 255, 255],
                QRMatrix::M_SEPARATOR_DARK => [255, 255, 255],
                QRMatrix::M_ALIGNMENT_DARK => [255, 255, 255],
                QRMatrix::M_TIMING_DARK => [255, 255, 255],
                QRMatrix::M_FORMAT_DARK => [255, 255, 255],
                QRMatrix::M_VERSION_DARK => [255, 255, 255],
                QRMatrix::M_QUIETZONE_DARK => [255, 255, 255],
                QRMatrix::M_LOGO_DARK => [255, 255, 255],
                QRMatrix::M_FINDER_DOT => [255, 255, 255],
            ],
        ]);

        (new QRCode($options))->render($url, $path);

        return $path;
    }

    private function publicPathFromUrl(?string $url): ?string
    {
        if (!$url) {
            return null;
        }

        $path = parse_url($url, PHP_URL_PATH) ?: $url;

        return public_path(ltrim($path, '/'));
    }

    private function deleteOldThumb(?string $oldPath, string $newPath): void
    {
        if (!$oldPath || $oldPath === '/'.$newPath) {
            return;
        }

        $path = public_path(ltrim($oldPath, '/'));

        if (is_file($path)) {
            @unlink($path);
        }
    }
}
