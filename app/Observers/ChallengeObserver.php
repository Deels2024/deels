<?php

namespace App\Observers;

use App\Helpers\AppHelper;
use App\Jobs\Moderation\CheckImage;
use App\Jobs\Moderation\CheckText;
use App\Jobs\Moderation\CheckVideo;
use App\Jobs\NotifyAllChannels;
use App\Jobs\SendTGChallengeModeration;
use App\Models\Challenge;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ChallengeObserver
{
    /**
     * Handle the Challenge "created" event.
     *
     * @param \App\Models\Challenge $challenge
     * @return void
     */
    public function created(Challenge $challenge)
    {
        $challenge->set_all_moderated('false');
        $text = [];
        if ($challenge->title) {
            $text[] = $challenge->title;
        }
        if ($challenge->description) {
            $text[] = $challenge->description;
        }
        if (!empty($text)) {
            CheckText::dispatch($challenge, implode(' ', $text));
        }
        if ($challenge->media) {
            if ($challenge->media->type == 'image') {
                CheckImage::dispatch($challenge, $challenge->getFile(true));
                $challenge->set_moderated('video');
            }
            if ($challenge->media->type == 'video') {
                CheckVideo::dispatch($challenge, $challenge->media);
                $challenge->set_moderated('image');
            }
        }
    }

    /**
     * Handle the Challenge "updating" event.
     *
     * @param \App\Models\Challenge $challenge
     * @return void
     */
    public function updating(Challenge $challenge)
    {
        $moderated = $challenge->isModerated();
        try {
            if ($moderated['checked']) {
                if(!$challenge->ai_moderated) {
                    $challenge->ai_moderated = true;
                    $helper = new AppHelper();
                    $helper->challenge_approve($challenge);
                }
            }
        } catch (\Throwable $e) {

        }
    }

    /**
     * Handle the Challenge "updated" event.
     *
     * @param \App\Models\Challenge $challenge
     * @return void
     */
    public function updated(Challenge $challenge)
    {
        if ($challenge->wasChanged('active') && $challenge->active == 0) {

            if($challenge->wasChanged('declined')) {

            } else {
                $challenge->declined = false;
            }
            $challenge->blocked_at = null;
            $challenge->saveQuietly();
            $textChanged = $challenge->wasChanged('title') || $challenge->wasChanged('description');
            $mediaChanged = $challenge->wasChanged('media_id');
            $text = [];

            if ($challenge->title) {
                $text[] = $challenge->title;
            }
            if ($challenge->description) {
                $text[] = $challenge->description;
            }
            if ($textChanged && !empty($text)) {
                $challenge->set_moderated('text', false);
                CheckText::dispatch($challenge, implode(' ', $text));
            }
            if($mediaChanged && $challenge->media) {
                if ($challenge->media->type == 'image') {
                    $challenge->set_moderated('image', false);
                    CheckImage::dispatch($challenge, $challenge->getFile(true));
                    $challenge->set_moderated('video');
                }
                if ($challenge->media->type == 'video') {
                    $challenge->set_moderated('video', false);
                    CheckVideo::dispatch($challenge, $challenge->media);
                    $challenge->set_moderated('image');
                }
            }

        }

        if ($challenge->isDirty('ai_moderated') && $challenge->ai_moderated == 1) {
            $moderated = $challenge->isModerated();
            try {
                if ($moderated['valid']) {

                } else {
                    if(!$challenge->declined) {
                        SendTGChallengeModeration::dispatch($challenge);
                        NotifyAllChannels::dispatch($challenge->user_id, 'Ваш челлендж был автоматически заблокирован в связи с нарушениями правил публикации на сайте. Для того,чтобы внести изменения для повторной модерации, нажмите на значок редактирования на вашем челлендже в соответствующем разделе в личном кабинете');
                    }
                    $challenge->declined = true;
                    $challenge->active = false;
                    $challenge->blocked_at = Carbon::now();
                    $challenge->saveQuietly();
                }
            } catch (\Throwable $e) {

            }
        }
    }

    /**
     * Handle the Challenge "deleted" event.
     *
     * @param \App\Models\Challenge $challenge
     * @return void
     */
    public function deleted(Challenge $challenge)
    {
        try {
            $media = $challenge->media;
            if ($media) {
                $path = 'uploads/challenges/';
                $stories_file = $path . $media->slug_ext;
                \Storage::disk('public')->delete($stories_file);
            }
            \Storage::disk('public')->deleteDirectory($path . 'thumbs/challenge_' . $challenge->id);
            $media->delete();
        } catch (\Throwable $e) {

        }
    }

    /**
     * Handle the Challenge "restored" event.
     *
     * @param \App\Models\Challenge $challenge
     * @return void
     */
    public function restored(Challenge $challenge)
    {
        //
    }

    /**
     * Handle the Challenge "force deleted" event.
     *
     * @param \App\Models\Challenge $challenge
     * @return void
     */
    public function forceDeleted(Challenge $challenge)
    {
        //
    }
}
