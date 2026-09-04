<?php

declare(strict_types=1);

namespace App\Models;

use App\Helpers\AppHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class Comment extends Model
{
    protected $guarded = [];

    protected $appends = array('username','avatar_url');

    protected static function boot()
    {
        parent::boot();

        static::created(function (Comment $comment) {
            $helper = new AppHelper();
            $helper->save_action('comment', $comment->user_id, $comment->campaign ?? $comment->story);
        });

        static::addGlobalScope('with_users', function (Builder $builder) {
            $builder->with('user');
        });

        static::creating(function ($comment) {
            $helper = new AppHelper();
            if($comment->story_id) {
                $text = 'Пользователь '.$comment->user->name.' добавил комментарий к вашей сторис';
                $button = [
                    'type' => 'story',
                    'story_id' => $comment->story_id,
                    'text' => 'Перейти',
                    'url' => $comment->story->getStoryDashboardUrl()
                ];
                $helper->chat_notify($comment->story->user,$text,$button);
            }

        });
        static::updated(function ($comment) {
            $helper = new AppHelper();
            if($comment->campaign_id && $comment->approved) {
                $text = 'Пользователь '.$comment->user->name.' добавил комментарий к вашей копилке';
                $button = [
                    'type' => 'campaign',
                    'campaign_id' => $comment->campaign_id,
                    'text' => 'Перейти',
                    'url' => route('deels.public.campaigns.show', ['slug' => $comment->campaign->slug])
                ];
                $helper->chat_notify($comment->campaign->user,$text,$button);
            }
        });

    }

    public function scopeApproved($query)
    {
        return $query->whereApproved('1');
    }

    public function scopeParent($query)
    {
        return $query->whereCommentId(0);
    }

    public function author()
    {
        $author = $this->belongsTo(User::class, 'user_id')->withTrashed();
        if($author) {
            return $this->belongsTo(User::class, 'user_id')->withDefault(function ($user) {
                $user->id = 0;
                $user->name = 'Пользователь удален';
                $user->avatar = '/default_avatars/avatar_6.png';
            });
        }
        return $author;
    }

    public function user()
    {
        $user = $this->belongsTo(User::class, 'user_id')->withTrashed();
        if($user) {
            return $this->belongsTo(User::class, 'user_id')->withDefault(function ($user) {
                $user->id = 0;
                $user->name = 'Пользователь удален';
                $user->avatar = '/default_avatars/avatar_6.png';
            });
        }
        return $user;
    }



    public function childs_approved()
    {
        return $this->hasMany(ChildComment::class, 'comment_id', 'id')->whereApproved('1')->orderBy('id', 'desc');
    }

    public function likes()
    {
        return $this->hasMany(Likes::class, 'comment_id', 'id');
    }



    public function created_at_datetime()
    {
        $created_date_time = $this->created_at->timezone(get_option('default_timezone'))->format(get_option('date_format_custom').' '.get_option('time_format_custom'));

        return $created_date_time;
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function story()
    {
        return $this->belongsTo(Story::class)->withoutGlobalScopes();
    }

    public function isLiked($user_id) {
        $like = Likes::where('comment_id',$this->id)->where('user_id',$user_id)->first();
        if($like) {
            return  true;
        }
        return false;
    }

    public function getUsernameAttribute()
    {
        $attribute = null;
        if ($this->user) {
            return $this->user->username;
        }

        return $attribute;
    }
    public function getAvatarUrlAttribute()
    {
        $attribute = null;
        if ($this->user) {
            return $this->user->avatar_url;
        }

        return $attribute;
    }



}
