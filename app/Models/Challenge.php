<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\Moderate;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Pawlox\VideoThumbnail\VideoThumbnail;

/**
 * Class Campaign.
 */
class Challenge extends Model
{
    use Moderate;

    /** @var array */
    public $guarded = [];

    protected $appends = array('path', 'filepath', 'type', 'thumbnail', 'video_preview', 'views_count', 'likes_count', 'comments_count', 'end_days', 'participants', 'status_title');

    protected $casts = [
        'moderation' => 'array',
        'date_from' => 'datetime',
        'date_to' => 'datetime',
        'winner_selection_deadline' => 'datetime',
        'winner_selected_at' => 'datetime',
        'participants_count' => 'integer',
        'reward_amount' => 'integer',
        'winner_decided_by_user_id' => 'integer',
        'invite_user_ids' => 'array',
    ];

    public function scopeActive($query)
    {
        $now = Carbon::now();
        return $query->where('challenges.active', 1)
            ->where('challenges.declined', 0)
            ->whereNull('challenges.blocked_at')
            ->where('challenges.finished', false);
//            ->where('challenges.start', '>=', $now);
    }

    public function user(): BelongsTo
    {
        $user = $this->belongsTo(User::class);
        if ($this->user_id === 0) {
            return $this->belongsTo(User::class)->withDefault(function ($user) {
                $user->id = 0;
                $user->name = 'DEELS';
                $user->avatar = '/default_avatars/robot.jpeg';
            });
        }

        if ($user) {
            return $this->belongsTo(User::class)->withDefault(function ($user) {
                $user->id = 0;
                $user->name = 'Пользователь удален';
                $user->avatar = '/default_avatars/avatar_1.png';
            });
        }
        return $user;
    }

    public function winners()
    {
        return $this->belongsToMany(User::class);
    }

    public function stories(): HasMany
    {
        return $this->hasMany(Story::class, 'challenge_id')
            ->where('is_useful', false)
            ->where(function ($query): void {
                $query->where('is_main_story', false)
                    ->orWhereNull('is_main_story');
            });
    }

    public function usefulStories(): HasMany
    {
        return $this->hasMany(Story::class, 'challenge_id')
            ->where('is_useful', true);
    }

    public function getMainStory(): HasOne
    {
        return $this->hasOne(Story::class, 'challenge_id')
            ->withoutGlobalScopes()
            ->where('is_main_story', true);
    }

    public function views()
    {
        return $this->hasManyThrough(View::class, Story::class);
    }

    public function comments()
    {
        return $this->hasManyThrough(Comment::class, Story::class);
    }

    public function likes()
    {
        return $this->hasManyThrough(Likes::class, Story::class);
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'media_id');
    }

    public function getFile($path = false)
    {
        if (!$this->media) {
            return null;
        }

        $filePath = $this->media->folder
            ? $this->media->folder.'/'.$this->media->slug_ext
            : 'uploads/challenges/'.$this->media->slug_ext;

        return $path ? $filePath : url($filePath);
    }

    public function getPathAttribute()
    {
        if ($this->media) {
            return $this->getTypeAttribute() === 'video'
                ? route('challenges.get.video', $this->id)
                : $this->getFile();
        } else {
            return null;
        }
    }

    public function getFilePathAttribute()
    {
        return $this->getFile();
    }

    public function getTypeAttribute()
    {
        if ($this->media) {
            $type = 'image';
            if (Str::contains($this->media->mime_type, 'video')) {
                $type = 'video';
            }
            return $type;
        }

        $mainStory = $this->getMainStory()->first();
        if ($mainStory) {
            return $mainStory->type;
        }

        return null;

    }

//
//    public function getDeclinedAttribute(){
//        return $this->declined ?? false;
//    }

    public function getThumbnailAttribute()
    {
        $thumbnail = null;
        if ($this->media && $this->getTypeAttribute() == 'video') {
            if ($this->media->thumbnail) {
                $thumbnail = url($this->media->thumbnail);
            } else {
                $mediaPath = $this->media->folder
                    ? rtrim($this->media->folder, '/') . '/'
                    : 'uploads/challenges/';
                $videoUrl = public_path($mediaPath . $this->media->slug_ext);
                if (file_exists($videoUrl)) {
                    $file_path = 'uploads/stories/thumbs/challenge_' . $this->id . '/';
                    $path = public_path($file_path);
                    if (!File::isDirectory($path)) {
                        File::makeDirectory($path, 0777, true, true);
                    }
                    $fileName = 'thumb_' . $this->media->slug . '.jpg';
                    if (!file_exists($path . $fileName)) {
                        $video_thumbnail = new VideoThumbnail();
                        $video_thumbnail->createThumbnail(
                            $videoUrl,
                            $path,
                            $fileName,
                            0,
                            $width = 607,
                            $height = 1080
                        );
                    }
                    $this->media->thumbnail = $file_path . $fileName;
                    $this->media->save();
                    $thumbnail = url($this->media->thumbnail);
                }
            }
        }
        if (!$thumbnail && $this->media && $this->getTypeAttribute() == 'image' && $this->media->thumbnail) {
            $thumbnail = url($this->media->thumbnail);
        }
        if ($this->cover) {
            $thumbnail = url($this->cover);
        }
        if (!$thumbnail) {
            $mainStory = $this->getMainStory()->first();
            if ($mainStory) {
                $mainStory->ensureStoryPreview();
                $thumbnail = $mainStory->thumbnail;
            }
        }
        return $thumbnail;
    }

    public function getVideoPreviewAttribute()
    {
        $preview = null;
        if ($this->media && $this->getTypeAttribute() == 'video') {
            if ($this->media && $this->media->video_preview) {
                $preview = url($this->media->video_preview);
            } else {
                $mainStory = $this->getMainStory()->first();
                if ($mainStory && (int) $mainStory->media_id === (int) $this->media_id) {
                    $mainStory->ensureStoryPreview();
                    $this->media->refresh();
                    if ($this->media->video_preview) {
                        $preview = url($this->media->video_preview);
                    }
                }
            }
        }
        if (!$preview) {
            $mainStory = $this->getMainStory()->first();
            if ($mainStory) {
                $mainStory->ensureStoryPreview();
                $preview = $mainStory->video_preview;
            }
        }

        return $preview;
    }

    public function getEndDaysAttribute()
    {
        $now = Carbon::now();
        $finish = Carbon::parse($this->finish)->addDay();
        if ($now < $finish) {
            return $finish->diffInDays($now);
        }
        return 0;
    }

    public function daysLeft()
    {
        if (Carbon::now() > $this->finish) {
            return null;
        }
        return $this->end_days . ' ' . trans_choice('numbers.days', $this->end_days);
    }

    public function participant($user_id)
    {
        return Story::where('user_id', $user_id)
            ->where('challenge_id', $this->id)
            ->whereNull('withdrawn_at')
            ->where(function ($query): void {
                $query->where('is_main_story', false)
                    ->orWhereNull('is_main_story');
            })
            ->first();
    }

    public function getViewsCountAttribute()
    {
        $count = array_key_exists('views_count', $this->attributes)
            ? (int) $this->attributes['views_count']
            : $this->views()->count();

        if ($count >= 1000) {
            $count = ($count / 1000) . 'K';
        }
        return $count;
    }

    public function getCommentsCountAttribute()
    {
        $count = array_key_exists('comments_count', $this->attributes)
            ? (int) $this->attributes['comments_count']
            : $this->comments()->count();

        if ($count >= 1000) {
            $count = round(($count / 1000), 2) . 'K';
        }

        return $count;
    }

    public function getLikesCountAttribute()
    {
        $count = array_key_exists('likes_count', $this->attributes)
            ? (int) $this->attributes['likes_count']
            : $this->likes()->count();

        if ($count >= 1000) {
            $count = round(($count / 1000), 2) . 'K';
        }

        return $count;
    }

    public function getParticipantsAttribute()
    {
        $participantIds = $this->stories()
            ->active()
            ->pluck('user_id')
            ->filter()
            ->map(static fn ($id): int => (int) $id);

        if (\Illuminate\Support\Facades\Schema::hasTable('contest_participations')) {
            $storedParticipantIds = \Illuminate\Support\Facades\DB::table('contest_participations')
                ->where([
                    'contest_type' => 'challenge',
                    'contest_id' => $this->id,
                    'status' => 'active',
                ])
                ->pluck('user_id')
                ->map(static fn ($id): int => (int) $id);

            $participantIds = $participantIds->merge($storedParticipantIds);
        }

        return $participantIds->unique()->count();

    }

    public function getStatusTitleAttribute()
    {
        if ($this->finished) {
            if ($this->completion_status === 'skipped') {
                return 'Пропущен';
            }
            return 'Завершен';
        }
        if ($this->frozen) {
            return 'Заморожен';
        }
        if (!$this->started) {
            $requiredParticipants = (int) ($this->min_participants ?? 0);
            return $requiredParticipants <= 0 || $this->participants >= $requiredParticipants
                ? 'Запланирован'
                : 'Идет набор';
        }

        return 'Длится';
    }


}
