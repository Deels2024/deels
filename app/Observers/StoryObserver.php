<?php

namespace App\Observers;

use App\Helpers\AppHelper;
use App\Jobs\Moderation\CheckImage;
use App\Jobs\Moderation\CheckText;
use App\Jobs\Moderation\CheckVideo;
use App\Jobs\NotifyAllChannels;
use App\Jobs\SendTGStoryModeration;
use App\Jobs\Stories\ProcessVideo;
use App\Models\Story;
use App\Models\User;
use App\Services\Cdnvideo\CdnvideoClient;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StoryObserver
{
    /**
     * Handle the Story "created" event.
     *
     * @param \App\Models\Story $story
     * @return void
     */
    public function creating(Story $story)
    {
        $types = ['text', 'image', 'video'];
        $moderation = $this->moderation ?? [];
        foreach ($types as $type) {
            $moderation[$type]['status'] = false;
            $moderation[$type]['checked'] = false;
        }
        $story->moderation = $moderation;
        $story->ai_moderated = false;
        $story->is_converted = false;
    }

    public function created(Story $story)
    {

        $story->set_all_moderated('false');
        if ($story->description) {
            CheckText::dispatch($story, $story->description);
        } else {
            $story->set_moderated('text');
        }
        if ($story->cover) {
            CheckImage::dispatch($story, $story->cover);
            $story->set_moderated('video');
        }
        if ($story->media && $story->type === 'video') {
            CheckVideo::dispatch($story, $story->media);
            $story->set_moderated('image');
        }
        if ($story->media && $story->type === 'image') {
            CheckImage::dispatch($story, $story->getFile(true));
            $story->set_moderated('video');
            $story->is_converted = true;
            $story->saveQuietly();
        }

        if ($story->type === 'video') {
            ProcessVideo::dispatch($story->id)->delay(now()->addSecond(5));
        }

    }

    public function updating(Story $story)
    {
        if ($story->isDirty('active') && $story->active == 1) {
            $story->declined = false;
        }

        $challenge = $story->challenge;

        $story_log = [];

        if ($story->isDirty('frozen') && $challenge) {
            $story_log[] = 'Отправляем на проверку story ' . $story->id;
            if ($story->frozen) {
                $challenge->frozen = true;
                $challenge->frozen_at = Carbon::now();
                $story_log[] = 'Остановка челленджа ' . $challenge->id;
            } else {
                if (!$challenge->finished) {
                    foreach ($challenge->stories as $story) {
                        NotifyAllChannels::dispatch($story->user_id, 'Челлендж "' . $challenge->title . '" снова активен!', 'Челлендж активен!');
                    }
                }
                $challenge->frozen = false;
                $challenge->frozen_at = null;
                $story_log[] = 'Разморозка челленджа ' . $challenge->id;
            }

            $challenge->save();
        }

        if ($story->isDirty('banned') && $challenge) {
            $story->banned_reason = 'Нарушение правил';
            $story->saveQuietly();

            // Бан пользователя на 30 дней
            $story->user->banned_till = Carbon::now()->addDays(30);
            $story->user->save();
            $banned_user_id = $story->user_id;

            $story_log[] = 'Баним сторис ' . $story->id;
            $story_log[] = 'Баним пользователя ' . $story->user_id;

            try {
                $notify_text = 'По результатам проверки Вашей сторис №' . $story->id . ' было выявлено грубое нарушение правил сообщества,в связи с чем сторис заблокирована, а Ваш аккаунт забанен';
                NotifyAllChannels::dispatch($story->user_id, $notify_text, 'Сторис заблокирована!', true);
            } catch (\Throwable $e) {

            }

            if ($challenge->finished) {
                $current_winners = $challenge->winners()->pluck('user_id')->toArray();

                if (in_array($story->user_id, $current_winners)) {
                    $story_log[] = 'пользователь в списке победителей ' . $challenge->id;
                    $challenge->winners()->detach($story->user_id);

                    $win_transaction = DB::table('transactions')->where('meta', 'like', '%"description":"Победа в челлендже%')->where('meta', 'like', '%' . $challenge->title . '%')->where('payable_id', $story->user_id)->first();

                    if ($win_transaction) {
                        $story_log[] = 'транзакция за победу челлендж ' . $challenge->id . ' найдена';
                        $amount = $win_transaction->amount;
                        $payments_wallet = $story->user->getWallet('payments');
                        $default_wallet = $story->user->getWallet('default');
                        try {
                            $story_log[] = 'вычитаем из кошелька для оплат ' . $amount . ' у пользователя ' . $story->user_id;
                            $payments_wallet->withdraw(intval($amount), ['create' => 'challenge', 'description' => 'Возврат за победу в челлендже: ' . $challenge->title]);
                        } catch (\Throwable $e) {
                            $story_log[] = $e->getMessage();
                            $story_log[] = 'вычитаем из кошелька для вывода ' . $amount . ' у пользователя ' . $story->user_id;
                            try {
                                $default_wallet->withdraw(intval($amount), ['create' => 'challenge', 'description' => 'Возврат за победу в челлендже: ' . $challenge->title]);
                            } catch (\Throwable $e) {
                                $story_log[] = $e->getMessage();
                            }
                        }
                    }

                    if (count($current_winners) > 1) {
                        $story_log[] = 'Победителей челленджа ' . $challenge->id . ' несколько';
                        if (isset($amount)) {
                            $current_winners = $challenge->winners()->pluck('user_id')->toArray();

                            $current_winners = array_filter($current_winners, function ($value) use ($banned_user_id) {
                                return $value !== $banned_user_id;
                            });
                            $prize = intval(ceil($amount / count($current_winners)));
                            $winners = User::whereIn('id', $current_winners)->get();
                            $story_log[] = 'начисляем остальным победителям челленджа ' . $challenge->id . ' по ' . $prize . ' дилсов';
                            foreach ($winners as $winner) {
                                $story_log[] = 'начисляем пользователю ' . $winner->id . ' по ' . $prize . ' дилсов';
                                $winner->deposit($prize, ['get' => 'coins', 'description' => 'Начисление за победу в челлендже "' . $challenge->title . '"']);
                            }

                        }
                    }
                }

            }

            $challenge->frozen = false;
            $challenge->frozen_at = null;
            $challenge->save();

        }

        $moderated = $story->isModerated();
        try {
            if ($moderated['checked']) {
                if (!$story->ai_moderated) {
                    $story->ai_moderated = true;
                    $helper = new AppHelper();
                    $helper->story_approve($story);
                }
            }
        } catch (\Throwable $e) {

        }

        if (!empty($story_log)) {
            $story_log[] = 'Осуществление проверки ответа на челлендж:';
            Log::info(implode("\n", $story_log));
        }
    }

    /**
     * Handle the Story "updated" event.
     *
     * @param \App\Models\Story $story
     * @return void
     */
    public function updated(Story $story)
    {
        if ($story->isDirty('ai_moderated') && $story->ai_moderated == 1) {
            $moderated = $story->isModerated();
            try {
                if ($moderated['valid']) {

                } else {
                    if (!$story->declined) {
                        SendTGStoryModeration::dispatch($story);
                        NotifyAllChannels::dispatch($story->user_id, 'Ваша сторис была автоматически заблокирована в связи с нарушениями правил публикации на сайте');
                    }
                    $story->declined = true;
                    $story->active = false;
                    $story->saveQuietly();
                }
            } catch (\Throwable $e) {

            }
        }
    }

    /**
     * Handle the Story "deleted" event.
     *
     * @param \App\Models\Story $story
     * @return void
     */
    public function deleting(Story $story)
    {
        if($story->is_ad) {
            return false;
        }
    }
    public function deleted(Story $story)
    {
        if(!$story->is_ad) {
            try {
                $media = $story->media;
                if ($media) {
                    $path = 'uploads/stories/';
                    $stories_file = $path . $media->slug_ext;
                    \Storage::disk('public')->delete($stories_file);
                    \Storage::disk('public')->deleteDirectory($path . 'thumbs/story_' . $story->id);
                }

                if ($media) {
                    try {
                        $mediaFolder = $this->normalizeStoragePath($media->folder);
                        if ($mediaFolder && $media->type === 'video') {
                            $safeVideoFolder = $this->getSafeStoryVideoFolder($mediaFolder, $story);
                            if ($safeVideoFolder) {
                                \Storage::disk('public')->deleteDirectory($safeVideoFolder);
                            } else {
//                                Log::warning('Skipped', [
//                                    'story_id' => $story->id,
//                                    'media_id' => $media->id,
//                                    'folder' => $media->folder,
//                                ]);
                            }
                        } elseif ($mediaFolder && $media->type === 'image') {
                            \Storage::disk('public')->delete($mediaFolder . '/' . $media->slug_ext);
                            \Storage::disk('public')->delete($mediaFolder . '/' . $media->slug . '.webp');
                        }
                    } catch (\Throwable $e) {

                    }

                    $media->delete();
                }
                Log::info('Story ID ' . $story->id . ' is deleted');
            } catch (\Throwable $e) {

            }

//            try {
//                $media_data = $story->media->cdn_profiles;
//                if (isset($media_data['dir'])) {
//                    $cdn_client = new CdnvideoClient();
//                    $accounts = $cdn_client->get('app/inventory/v1/accounts/');
//                    $account_name = $accounts[0]['name'];
//                    $cdn_client->delete('app/storage/v1/' . $account_name . '/files/' . $media_data['dir'], ['dir' => true]);
//                }
//            } catch (\Throwable $e) {
//
//            }
        }
    }

    /**
     * Handle the Story "restored" event.
     *
     * @param \App\Models\Story $story
     * @return void
     */
    public function restored(Story $story)
    {
        //
    }

    /**
     * Handle the Story "force deleted" event.
     *
     * @param \App\Models\Story $story
     * @return void
     */
    public function forceDeleted(Story $story)
    {
        //
    }

    private function normalizeStoragePath(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        $path = str_replace('\\', '/', $path);
        $path = preg_replace('#/+#', '/', $path);
        $path = trim($path, '/');

        return $path !== '' ? $path : null;
    }

    private function getSafeStoryVideoFolder(string $folder, Story $story): ?string
    {
        $expectedFolder = 'uploads/stories/' . $story->id;

        if ($folder === $expectedFolder || str_starts_with($folder, $expectedFolder . '/')) {
            return $folder;
        }

        return null;
    }
}
