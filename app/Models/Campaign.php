<?php

declare(strict_types=1);

namespace App\Models;

use App\Jobs\Campaigns\ChangeCampaignHealth;
use App\Services\CampaignShareThumbService;
use App\Traits\Moderate;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;
use Spatie\Sitemap\Contracts\Sitemapable;
use Spatie\Sitemap\Tags\Url;

/**
 * Class Campaign.
 */
class Campaign extends Model
{
    use Moderate;

    /** @var array */
    public $guarded = [];

    /** @var string[] */
    protected $dates = ['end_date'];

    /** @var string[] */
    protected $casts = [
        'tags' => 'array',
        'images' => 'array',
        'status' => 'integer',
        'health' => 'integer',
        'moderation' => 'array',
        'is_edited' => 'boolean',
    ];

    public const STATUS_SLEEPING = 4;
    public const STATUS_FINISHED = 5;
    public const STATUS_ARCHIVED = 6;

    public const HEALTH_MIN = 0;
    public const HEALTH_MAX = 10;

    public static function healthUp(int $value, ?int $userId = null): void
    {
        self::dispatchHealthChange($userId ?? (int) auth()->id(), abs($value));
    }

    public static function healthDown(int $value, ?int $userId = null): void
    {
        self::dispatchHealthChange($userId ?? (int) auth()->id(), -abs($value));
    }

    private static function dispatchHealthChange(int $userId, int $value): void
    {
        if ($userId === 0 || $value === 0) {
            return;
        }

        ChangeCampaignHealth::dispatch($userId, $value);
    }


    public static function formatCampaignData($campaign, $user = true)
    {
        $end_date = Carbon::parse($campaign->end_date);
        $now = Carbon::now();
        $days_left = !$end_date->isPast() ? $now->diffInDays($end_date) : null;

        $data = [
            'id' => $campaign->id,
            'title' => $campaign->title ?? null,
            'slug' => $campaign->slug ?? null,
            'short_description' => $campaign->short_description ?? null,
            'description' => $campaign->description ?? null,
            'preview' => $campaign->feature_img_url()->feature_image,
            'goal' => $campaign->goal,
            'donated' => $campaign->success_payments->sum('amount'),
            'sponsors' => $campaign->success_payments->count(),
            'days_left' => $days_left,
            'status' => $campaign->status,
            'health' => $campaign->health,
            'is_funded' => $campaign->is_funded,
            'percent_raised' => $campaign->percent_raised(),
            'user' => $campaign->user->getUserData()
        ];

        return $data;
    }


    public function scopeWithCampaignData(Builder $query)
    {
        return $query->with(['success_payments', 'feature_img_url'])
            ->get()
            ->map(function ($campaign) {
                $end_date = Carbon::parse($campaign->end_date);
                $now = Carbon::now();
                $days_left = !$end_date->isPast() ? $now->diffInDays($end_date) : null;

                return [
                    'id' => $campaign->id,
                    'title' => $campaign->title ?? null,
                    'slug' => $campaign->slug ?? null,
                    'short_description' => $campaign->short_description ?? null,
                    'description' => $campaign->description ?? null,
                    'preview' => $campaign->feature_img_url()->feature_image,
                    'goal' => $campaign->goal,
                    'donated' => $campaign->success_payments->sum('amount'),
                    'sponsors' => $campaign->success_payments->count(),
                    'days_left' => $days_left,
                    'status' => $campaign->status,
                    'health' => $campaign->health,
                    'is_funded' => $campaign->is_funded,
                ];
            });
    }


    public function toSitemapTag(): Url|string|array
    {

        return Url::create($this->getUrl())
            ->setPriority(0.9)
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
            ->setLastModificationDate($this->updated_at);
    }

    public function get_category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function feature_media(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'feature_image');
    }

    public function amount_prefilled(): array|bool
    {
        $amount = $this->amount_prefilled;
        if ($amount) {
            return explode(',', $amount);
        }

        return false;
    }

    public function feature_img_url($full_size = false): object
    {
        return media_image_uri($this->feature_media, $this->slug);
    }

    public function shareThumbUrl(): string
    {
        if (!$this->share_thumb) {
            $shareThumb = app(CampaignShareThumbService::class)->generate($this);

            if ($shareThumb) {
                $this->share_thumb = $shareThumb;
                $this->saveQuietly();
            }
        }

        return $this->share_thumb ? url($this->share_thumb) : $this->feature_img_url()->feature_image;
    }

    /** @return HasMany */
    public function rewards(): HasMany
    {
        return $this->hasMany(Reward::class);
    }

    /** @return HasMany */
    public function updates(): HasMany
    {
        return $this->hasMany(Update::class)->orderBy('created_at', 'desc');
    }

    /** @return HasMany */
    public function faqs(): HasMany
    {
        return $this->hasMany(Faq::class);
    }

    public function stories(): HasMany
    {
        return $this->hasMany(Story::class, 'campaign_id', 'id');
    }

    /** @return false|float|int */
    public function days_left()
    {
        return html_entity_decode('&#8734;');
        $diff = strtotime($this->end_date) - time(); // time returns current time in seconds

        if ($diff > 0) {
            return floor($diff / (60 * 60 * 24)); // seconds/minute*minutes/hour*hours/day)
        }
        //        return 0;
    }

    public function user(): BelongsTo
    {
        $user = $this->belongsTo(User::class)->withTrashed();
        if ($user) {
            return $this->belongsTo(User::class)->withDefault(function ($user) {
                $user->id = 0;
                $user->name = 'Пользователь удален';
                $user->avatar = '/default_avatars/avatar_6.png';
            });
        }
        return $user;
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'campaign_id', 'id')->orderBy('created_at', 'DESC');
    }

    public function percent_raised(): float|int
    {
        if (\in_array($this->slug, [
            'onlain-kurs-po-fotopozirovaniyu',
            'alisa',
            'elektrouselitel-rulya-dlya-sestyorki-3',
        ])) {
            return 100;
        }
        $raised = $this->success_payments_sum_amount ?? $this->success_payments()->sum('amount');
        $goal = $this->goal;

        $percent = 0;
        if ($raised > 0) {
            $percent = round(($raised * 100) / $goal, 2, \PHP_ROUND_HALF_DOWN);
        }

        return $percent;
    }

    public function success_payments()
    {
        return $this->hasMany(Payment::class, 'campaign_id', 'id')->whereStatus('success');
    }

    public function amount_raised(): object
    {
        $raised = $this->success_payments_sum_amount ?? $this->success_payments()->sum('amount');

        $user_commission_percent = $this->campaign_owner_commission;

        $user_commission = 0;
        $platform_owner_commission = 0;

        if ($raised > 0 && null != $user_commission_percent) {
            $user_commission = ($raised * $user_commission_percent) / 100;
            $platform_owner_commission = $raised - $user_commission;
        }

        return (object)[
            'amount_raised' => $raised,
            'campaign_owner_commission' => $user_commission,
            'platform_owner_commission' => $platform_owner_commission,
        ];
    }

    public function is_ended(): bool
    {
        if ('end_date' == $this->end_method) {
            if ($this->end_date < Carbon::today()->toDateString()) {
                return true;
            }
        } elseif ('goal_achieve' == $this->end_method) {
            $raised = $this->success_payments_sum_amount ?? $this->success_payments()->sum('amount');
            if ($this->goal <= $raised) {
                return true;
            }
        }

        return false;
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [1,3]);
    }

    public function scopeBlocked($query)
    {
        return $query->whereIn('status', [2]);
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', [0, 2, 3]);
    }

    public function scopeExpired($query)
    {
        return $query->whereDate('end_date', '<', Carbon::today()->toDateString());
    }

    public function scopeFunded($query)
    {
        return $query->where('is_funded', 1);
    }

    public function scopeWithGoalDone($query)
    {
        return $query->where('goal', '<=', $this->success_payments()->sum('amount'));
    }

    public function scopeStaff_picks($query)
    {
        return $query->where('is_staff_picks', 1);
    }

    public function requested_withdrawal(): HasOne
    {
        return $this->hasOne(WithdrawalRequest::class);
    }

    /** @return HasMany */
    public function likes(): HasMany
    {
        return $this->hasMany(Likes::class);
    }

    public function backers()
    {
        return User::whereIn('id', $this->success_payments()
            ->whereNotNull('user_id')
            ->pluck('user_id'))->get();
    }

    public function fullyDonated(): Builder
    {
        return $this->join(DB::raw(
            '(select sum(`payments`.`amount`) as total_amount, campaign_id
              from `payments` where status = "success" group by `campaign_id`) as p'
        ), function ($join) {
            $join->on('campaigns.id', '=', 'p.campaign_id')
                ->on('goal', '<=', 'p.total_amount');
        });
    }

    public function isLiked($user_id)
    {
        $like = Likes::where('user_id', $user_id)->where('campaign_id', $this->id)->first();
        if ($like) {
            return true;
        }
        return false;
    }

    public function getUrl()
    {
        return url(route('campaign_single', $this->slug));
    }

    public function getStatus()
    {
        $statuses = [
            0 => 'На модерации',
            1 => 'Активна',
            2 => 'Не прошла модерацию',
            3 => 'На модерации',
            self::STATUS_SLEEPING => 'Спит',
            self::STATUS_FINISHED => 'Завершена',
            self::STATUS_ARCHIVED => 'В архиве',
        ];
        return $statuses[$this->status] ?? '';
    }
}
