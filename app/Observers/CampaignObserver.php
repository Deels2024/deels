<?php

namespace App\Observers;

use App\Helpers\AppHelper;
use App\Jobs\Moderation\CheckImage;
use App\Jobs\Moderation\CheckText;
use App\Jobs\NotifyAllChannels;
use App\Jobs\SendTGCampaignModeration;
use App\Models\Campaign;
use App\Services\CampaignShareThumbService;
use Illuminate\Support\Facades\Log;

class CampaignObserver
{
    public function creating(Campaign $campaign)
    {
        $campaign->status = 0;
    }
    /**
     * Handle the Campaign "created" event.
     *
     * @param \App\Models\Campaign $campaign
     * @return void
     */
    public function created(Campaign $campaign)
    {
        $campaign->set_all_moderated('false');
        $text = [];
        if ($campaign->title) {
            $text[] = $campaign->title;
        }
        if ($campaign->description) {
            $text[] = $campaign->description;
        }
        if (!empty($text)) {
            CheckText::dispatch($campaign, implode(' ', $text));
        }
        if ($campaign->feature_media) {
            $path = parse_url($campaign->feature_img_url()->original);
            CheckImage::dispatch($campaign, $path['path']);
            $campaign->set_moderated('video');
        } else {
            $campaign->set_moderated('image');
        }

        if ($campaign->images) {
            foreach ($campaign->images as $k => $image) {
                $path = parse_url(media_image_uri($image)->original);
                CheckImage::dispatch($campaign, $path['path'], 'video');
            }
            $campaign->set_moderated('image');
        } else {
            $campaign->set_moderated('video');
        }

        $this->refreshShareThumb($campaign);
    }

    public function updating(Campaign $campaign)
    {
        $moderated = $campaign->isModerated();
        try {
            if ($moderated['checked']) {
                if(!$campaign->ai_moderated) {
                    $campaign->ai_moderated = true;
                    $helper = new AppHelper();
                    $helper->campaign_status($campaign->id, 'approve');
                }
            }
        } catch (\Throwable $e) {

        }
    }

    /**
     * Handle the Campaign "updated" event.
     *
     * @param \App\Models\Campaign $campaign
     * @return void
     */
    public function updated(Campaign $campaign)
    {
        if ($campaign->isDirty('status') && $campaign->status == 3) {
            $campaign->set_all_moderated('false');
            $campaign->ai_moderated = false;
            $text = [];
            if ($campaign->title) {
                $text[] = $campaign->title;
            }
            if ($campaign->description) {
                $text[] = $campaign->description;
            }
            if (!empty($text)) {
                CheckText::dispatch($campaign, implode(' ', $text));
            }
            if ($campaign->feature_media) {
                $path = parse_url($campaign->feature_img_url()->original);
                CheckImage::dispatch($campaign, $path['path']);
                $campaign->set_moderated('video');
            } else {
                $campaign->set_moderated('image');
            }

            if ($campaign->images) {
                foreach ($campaign->images as $k => $image) {
                    $path = parse_url(media_image_uri($image)->original);
                    CheckImage::dispatch($campaign, $path['path'], 'video');
                }
                $campaign->set_moderated('image');
            } else {
                $campaign->set_moderated('video');
            }
        }
        if ($campaign->isDirty('ai_moderated') && $campaign->ai_moderated == 1) {
            $moderated = $campaign->isModerated();
            try {
//                Log::info(['Campaign moderation',$campaign->id, $moderated]);
                if ($moderated['valid']) {

                    $campaign->status = 0;
                    $campaign->saveQuietly();
                } else {
                    if($campaign->status != 3) {
                        SendTGCampaignModeration::dispatch($campaign);
                        NotifyAllChannels::dispatch($campaign->user_id, 'Ваша копилка была автоматически заблокирована в связи с нарушениями правил публикации на сайте. Для того,чтобы внести изменения для повторной модерации, нажмите на значок редактирования на вашей копилке в соответствующем разделе в личном кабинете');
                    }
                    $campaign->status = 3;
                    $campaign->saveQuietly();

                }
            } catch (\Throwable $e) {
                Log::info(['Campaign moderation observer',$e->getMessage()]);
            }
        }

        if ($campaign->wasChanged('feature_image')) {
            $this->refreshShareThumb($campaign);
        }
    }

    private function refreshShareThumb(Campaign $campaign): void
    {
        $shareThumb = app(CampaignShareThumbService::class)->generate($campaign->fresh(['feature_media', 'user']));

        if ($shareThumb) {
            $campaign->share_thumb = $shareThumb;
            $campaign->saveQuietly();
        }
    }

    /**
     * Handle the Campaign "deleted" event.
     *
     * @param \App\Models\Campaign $campaign
     * @return void
     */
    public function deleted(Campaign $campaign)
    {
        //
    }

    /**
     * Handle the Campaign "restored" event.
     *
     * @param \App\Models\Campaign $campaign
     * @return void
     */
    public function restored(Campaign $campaign)
    {
        //
    }

    /**
     * Handle the Campaign "force deleted" event.
     *
     * @param \App\Models\Campaign $campaign
     * @return void
     */
    public function forceDeleted(Campaign $campaign)
    {
        //
    }
}
