<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\Moderate;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Bavix\Wallet\Traits\HasWallet;
use Bavix\Wallet\Interfaces\Customer;
use Bavix\Wallet\Interfaces\Taxable;
use Bavix\Wallet\Interfaces\ProductLimitedInterface;
use Pawlox\VideoThumbnail\VideoThumbnail;

/**
 * Class Campaign.
 */
class Story extends Model implements ProductLimitedInterface, Taxable
{
    use HasWallet;
    use Moderate;

    /** @var array */
    public $guarded = [];
    protected $appends = array('path', 'filepath', 'type', 'thumbnail', 'video_preview', 'story_preview', 'webp_filepath', 'views_count', 'likes_count', 'comments_count', 'votes', 'votes_data', 'hls_url');

    protected $casts = [
        'moderation' => 'array',
        'data' => 'array',
        'ads_data' => 'array',
        'is_main_story' => 'boolean',
        'is_useful' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function (Story $story): void {
            if (Schema::hasTable('contest_reports')) {
                ContestReport::where('story_id', $story->id)->update(['story_id' => null]);
            }
        });

        static::addGlobalScope('banned', function (Builder $builder) {
            $builder->with('comments', 'likes')->whereNull('battle_id')->where('broken', false);
        });
    }

    public function scopeActive($query)
    {
        return $query->where('stories.active', 1)
            ->where('stories.declined', 0)
            ->whereNull('stories.blocked_at')
            ->whereNull('stories.withdrawn_at')
            ->where('stories.broken', false);
    }

    public function scopeNotMainStory($query)
    {
        return $query->where(function ($query): void {
            $query->where('stories.is_main_story', false)
                ->orWhereNull('stories.is_main_story');
        });
    }

    public function scopeNotUseful($query)
    {
        return $query->where('stories.is_useful', false);
    }

    public function scopeExcludeBlockedAuthors($query, ?int $abusedBy = null)
    {
        if (!$abusedBy) {
            return $query;
        }

        return $query->whereNotIn('stories.user_id', function ($subQuery) use ($abusedBy): void {
            $subQuery->select('user_id')
                ->from('abuses')
                ->where('abused_by', $abusedBy)
                ->where('blocked', true);
        });
    }

    public function canBuy(Customer $customer, int $quantity = 1, bool $force = false): bool
    {
        /**
         * If the service can be purchased once, then
         *  return !$customer->paid($this);
         */
        return true;
    }

    public function getAmountProduct(Customer $customer): int|string
    {
        return $this->amount;
    }

    public function getMetaProduct(): ?array
    {
        return [
            'donate' => 'story',
            'description' => 'Донат в сторис #' . $this->id,
        ];
    }

    public function getFeePercent(): int
    {
        return 0;
    }

    /**
     * @return BelongsTo
     */
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

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    /**
     * @return BelongsTo
     */
    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'media_id');
    }

    public function challenge(): BelongsTo
    {
        return $this->belongsTo(Challenge::class, 'challenge_id');
    }

    public function battle(): BelongsTo
    {
        return $this->belongsTo(Battle::class, 'battle_id');
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class, 'campaign_id');
    }

    public function likes(): HasMany
    {
        return $this->hasMany(Likes::class, 'story_id');
    }

    public function dislikes(): HasMany
    {
        return $this->hasMany(Dislikes::class, 'story_id');
    }



    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'story_id')->withCount('likes')->orderBy('likes_count', 'desc');
    }

    public function getFile($path = false)
    {
        if (!$this->media) {
            return null;
        }

        $filePath = $this->media->folder
            ? $this->media->folder.'/'.$this->media->slug_ext
            : ($this->media->getRawOriginal('path') ?: 'uploads/stories/'.$this->media->slug_ext);

        $filePath = str_replace('//', '/', $filePath);
        return $path ? $filePath : url($filePath);
    }

    public function views(): HasMany
    {
        return $this->hasMany(View::class, 'story_id');
    }

    public function getPathAttribute()
    {
        if ($this->media) {
            return route('stories.get.video', $this->id);
        } else {
            return null;
        }
    }

    public function getFilePathAttribute()
    {
        return $this->getFile();
    }

    public function getWebpFilePathAttribute()
    {
        if (!$this->media || $this->getTypeAttribute() !== 'image') {
            return null;
        }

        return $this->media->webp_path_url;
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

        return null;

    }

    public function getThumbnailAttribute()
    {
        $thumbnail = null;
        if ($this->media && $this->getTypeAttribute() == 'video') {
            if ($this->media->thumbnail) {
                $thumbnail = url($this->media->thumbnail);
            } else {
                $videoUrl = public_path('uploads/stories/' . $this->media->slug_ext);
                $file_path = $this->media->folder ? $this->media->folder . '/' : 'uploads/stories/';
                $file_path = 'uploads/stories/';
                $file_name = $this->media->slug_ext;
                if($this->media->folder) {
                    $file_path = $this->media->folder.'/';
                }
                $videoUrl = public_path($file_path . $this->media->slug_ext);
                $file_path_original = str_replace('//', '/', $file_path);
                $videoUrl = str_replace('//', '/', $videoUrl);
                if(file_exists("$file_path_original/dash/video_1080p.mp4")) {
                    $videoUrl = public_path($file_path_original . 'dash/video_1080p.mp4');
                    $file_path = $file_path . 'dash';
                    $file_name = 'video_1080p.mp4';
                }
                if(file_exists("$file_path_original/".$this->id."/dash/video_1080p.mp4")) {
                    $videoUrl = public_path("$file_path_original/".$this->id."/dash/video_1080p.mp4");
                    $file_path = $file_path . '/'.$this->id.'/dash';
                    $file_name = 'video_1080p.mp4';
                }

                if(file_exists(public_path("$file_path_original/dash/video_1080p.mp4"))) {
                    $videoUrl = public_path("$file_path_original/dash/video_1080p.mp4");
                    $file_path = $file_path_original . 'dash';
                    $file_name = 'video_1080p.mp4';
                }

                if(file_exists("$file_path_original/dash/video_1080p_audio.mp4")) {
                    $videoUrl = public_path($file_path_original . 'dash/video_1080p_audio.mp4');
                    $file_path = $file_path_original . '/dash';
                    $file_name = 'video_1080p_audio.mp4';
                }
                if(file_exists("$file_path_original/".$this->id."/dash/video_1080p_audio.mp4")) {
                    $videoUrl = public_path("$file_path_original/".$this->id."/dash/video_1080p_audio.mp4");
                    $file_path = $file_path_original . '/'.$this->id.'/dash';
                    $file_name = 'video_1080p_audio.mp4';
                }

                if(file_exists(public_path("$file_path_original/dash/video_1080p_audio.mp4"))) {
                    $videoUrl = public_path("$file_path_original/dash/video_1080p_audio.mp4");
                    $file_path = $file_path_original . '/dash';
                    $file_name = 'video_1080p_audio.mp4';
                }


                $videoUrl = str_replace('//', '/', $videoUrl);
                $videoUrl = str_replace('//', '/', $videoUrl);
                $file_path = str_replace('//', '/', $file_path);
                $file_path = str_replace('//', '/', $file_path);
                // Очистка путей от двойных слешей
                $videoUrl = str_replace('//', '/', $videoUrl);
                $file_path = str_replace('//', '/', $file_path);
                if (file_exists($videoUrl)) {
                    $file_path = 'uploads/stories/thumbs/story_' . $this->id . '/';
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
        if ($this->cover) {
            if (file_exists($this->cover)) {
                $thumbnail = url($this->cover);
            }
        }
        if($thumbnail) {
            $string = $this->id.'/'.$this->id;
            $thumbnail = str_replace($string, (string)$this->id, $thumbnail);
        }

        if (!$thumbnail && $this->media && $this->getTypeAttribute() == 'image') {
            $thumbnail = $this->media->webp_path_url ?? $this->getFile();
        }

        return $thumbnail;
    }

    public function getVideoPreviewAttribute()
    {
        $preview = null;
        if ($this->media && $this->getTypeAttribute() == 'video' && $this->shouldUseVideoPreview()) {
            if ($this->media && $this->media->video_preview) {
                $preview = url($this->media->video_preview);
            } else {
                $preview = $this->generateVideoPreview();
            }
        }

        return $preview;
    }

    private function generateVideoPreview(): ?string
    {
        $videoPath = $this->getPreviewSourceVideoPath();
        if (!$this->media || !$videoPath || !file_exists($videoPath)) {
            return null;
        }

        try {
            $mediaPath = $this->media->folder ? rtrim($this->media->folder, '/') . '/' : 'uploads/stories/';
            $previewPath = ltrim(str_replace('//', '/', $mediaPath . 'preview_' . $this->media->slug_ext), '/');
            $previewFullPath = public_path($previewPath);

            if (!file_exists($previewFullPath)) {
                $ffmpeg = FFMpeg::create([
                    'ffmpeg.binaries' => env('FFMPEG'),
                    'ffprobe.binaries' => env('FFPROBE'),
                ]);
                $format = new \FFMpeg\Format\Video\X264('aac', 'libx264');
                $format->setKiloBitrate(20000);
                $format->setAdditionalParameters([
                    '-preset', 'slow',
                    '-crf', '22',
                    '-pix_fmt', 'yuv420p',
                    '-movflags', '+faststart',
                    '-vf', 'scale=trunc(iw/2)*2:trunc(ih/2)*2',
                ]);

                $ffmpeg->open($videoPath)
                    ->clip(TimeCode::fromSeconds(0), TimeCode::fromSeconds(3))
                    ->save($format, $previewFullPath);
            }

            $this->media->video_preview = $previewPath;
            $this->media->save();

            return url($previewPath);
        } catch (\Throwable $e) {
            \Log::error('Ошибка генерации превью видео сторис: ' . $e->getMessage());
            return null;
        }
    }

    private function getPreviewSourceVideoPath(): ?string
    {
        if (!$this->media) {
            return null;
        }

        $filePath = $this->media->folder ? rtrim($this->media->folder, '/') . '/' : 'uploads/stories/';
        $filePath = str_replace('//', '/', $filePath);
        $candidates = [
            $filePath . 'dash/video_1080p_audio.mp4',
            $filePath . 'dash/video_1080p.mp4',
            $filePath . $this->media->slug_ext,
        ];

        foreach ($candidates as $candidate) {
            $fullPath = public_path($candidate);
            if (file_exists($fullPath)) {
                return $fullPath;
            }
        }

        return null;
    }

    public function getStoryPreview(): array
    {
        $this->ensureStoryPreview();

        if ($this->getTypeAttribute() === 'video' && $this->shouldUseVideoPreview() && $this->video_preview) {
            return [
                'type' => 'video',
                'url' => $this->video_preview,
                'poster' => $this->thumbnail,
            ];
        }

        return [
            'type' => 'image',
            'url' => $this->getTypeAttribute() === 'video' ? $this->thumbnail : $this->path,
            'poster' => $this->thumbnail,
        ];
    }

    private function shouldUseVideoPreview(): bool
    {
        return (bool) $this->is_main_story && ((int) $this->challenge_id > 0 || (int) $this->battle_id > 0);
    }

    public function ensureStoryPreview(): void
    {
        if ($this->getTypeAttribute() !== 'video' || !$this->shouldUseVideoPreview()) {
            return;
        }

        $this->thumbnail;
        $this->video_preview;

        if ($this->media) {
            $this->media->refresh();
        }
    }

    public function getStoryPreviewAttribute(): array
    {
        return $this->getStoryPreview();
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

    public function getVotesAttribute()
    {
        $likes_count = $this->likes()->count();
        $dislikes_count = $this->dislikes()->count();
        $count = $likes_count+$dislikes_count;

        return $count;
    }

    public function getVotesDataAttribute()
    {
        $likes_count = $this->likes()->count();
        $dislikes_count = $this->dislikes()->count();


        return [
            'likes' => $likes_count,
            'dislikes' => $dislikes_count,
        ];
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

    public function getStoryDashboardUrl()
    {
        return route('user_stories') . '?show=' . $this->id;
    }

    public function getStoryShareUrl()
    {
        return route('stories.catalog') . '?show=' . $this->id;
    }

    public function getHlsUrlAttribute()
    {
        return $this->media->hls_url ?? null;
    }




}
