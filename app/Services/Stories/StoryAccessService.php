<?php

declare(strict_types=1);

namespace App\Services\Stories;

use App\Jobs\FireBaseEvent;
use App\Models\Likes;
use App\Models\Story;
use App\Models\User;
use App\Notifications\UserEmail;
use App\Services\ReferralBonusService;

class StoryAccessService
{
    public function resolve(Story $story, ?User $user, $userId, int $storyId, bool $onlyBody, bool $donate): array
    {
        $isLiked = false;
        $isViewed = false;
        $showStory = !$story->paid;

        if ($user && $user->blockedBy($story->user_id)) {
            $blocked = true;
        } else {
            $blocked = false;
        }

        if ($userId) {
            $view = \App\Models\View::where('user_id', $userId)->where('story_id', $storyId)->first();
            $storyOwner = $story->user;

            if ($userId != $storyOwner->id) {
                try {
                    if ($story->paid && $donate) {
                        $paymentsWallet = $user->getWallet('payments');
                        if ($story->amount > 0) {
                            $balance = intval($paymentsWallet->balance ?? 0);
                            $paymentsWallet->withdraw(intval($story->amount), ['donate' => 'story', 'balance_before' => $balance, 'description' => 'Донат в сторис #' . $story->id]);
                            app(ReferralBonusService::class)->awardForFirstDonate($user);
                            $storyOwner->deposit($story->amount, ['get' => 'story', 'description' => 'Донат в сторис #' . $story->id]);
                            FireBaseEvent::dispatch($storyOwner->id, 'Вашу платную сторис только что открыли!', $story->id, 'story');
                        }

                        $showStory = true;
                        $text = 'Кто-то открыл вашу платную сторис №' . $story->id . '.<br>Баланс кошелька можно проверить на сайте <a href="' . url('/') . '">deels.ru</a>';
                        $story->user->notify(new UserEmail('Кто-то открыл вашу платную сторис №' . $story->id, $text));
                    }
                } catch (\Throwable $e) {
                    if (!$onlyBody) {
                        return [
                            'response' => response()->json([
                                'success' => false,
                                'amount' => $story->amount,
                                'balance' => intval($user->balance),
                                'error' => $e->getMessage(),
                            ]),
                        ];
                    }

                    $showStory = false;
                }
            } else {
                if (!$story->paid) {
                    $showStory = true;
                }
            }

            if ($view) {
                $showStory = true;
            }

            if ($user && $user->is_admin()) {
                $showStory = true;
            }

            if ($showStory || $donate) {
                $view = \App\Models\View::create([
                    'user_id' => $userId,
                    'story_id' => $storyId,
                ]);
            }

            $like = Likes::where('story_id', $storyId)->where('user_id', $userId)->first();
            if (!$onlyBody || $view) {
                if ($story->paid) {
                    $showStory = false;
                    $isViewed = false;
                }
                if (!$story->paid) {
                    $showStory = true;
                    $isViewed = true;
                }
                if ($story->paid && $view) {
                    $showStory = true;
                    $isViewed = true;
                }
            }

            if ($like) {
                $isLiked = true;
            }
        } else {
            if ($user && !$user->is_admin()) {
                $view = new \App\Models\View();
                $view->story_id = $storyId;
                $view->save();
            }
        }

        if ($story->paid && !$isViewed) {
            $showStory = false;
        }

        return [
            'is_liked' => $isLiked,
            'is_viewed' => $isViewed,
            'show_story' => $showStory,
            'blocked' => $blocked,
        ];
    }
}
