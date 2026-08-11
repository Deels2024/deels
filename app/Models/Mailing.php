<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Mailing extends Model
{
    protected $guarded = [];

    protected $casts = [
        'users' => 'array',
        'date' => 'datetime',
    ];

    public function receivers()
    {
        return $this->hasMany(NewsletterMail::class, 'newsletter_id');
    }

    public function fails_count()
    {
//        if ($this->status === 'done') {
//            return Cache::remember("newsletter_{$this->id}_fails_count", now()->addMonth(), function () {
//                return $this->hasMany(NewsletterMail::class, 'newsletter_id')->where('status', 'fail')->count();
//            });
//        }

        return $this->hasMany(NewsletterMail::class, 'newsletter_id')->where('status', 'fail')->count();
    }

    public function success_count()
    {
//        if ($this->status === 'done') {
//            return Cache::remember("newsletter_{$this->id}_success_count", now()->addMonth(), function () {
//                return $this->hasMany(NewsletterMail::class, 'newsletter_id')->where('status', 'success')->count();
//            });
//        }

        return $this->hasMany(NewsletterMail::class, 'newsletter_id')->where('status', 'success')->count();
    }




    public function pending_count()
    {
        return $this->hasMany(NewsletterMail::class, 'newsletter_id')->where('status', 'pending')->count();
    }

    public function sending_count()
    {
        return $this->hasMany(NewsletterMail::class, 'newsletter_id')->where('status', 'sending')->count();
    }



    public function sent_count()
    {
        return $this->hasMany(NewsletterMail::class, 'newsletter_id')->where('status', '!=', 'pending')->count();
    }


    public function getStatusColor() {

        if($this->status == 'processing' && $this->pending_count() == 0 && $this->sending_count() == 0) {
            $this->status = 'done';
        }

        $statuses = [
            'draft' => 'dark',
            'pending' => 'light',
            'sent' => 'success',
            'done' => 'success',
            'processing' => 'warning',
            'fail' => 'danger',
            'cancelled' => 'danger',
        ];
        return $statuses[$this->status];
    }
    public function getStatus()
    {
        if($this->status == 'processing' && $this->pending_count() == 0 && $this->sending_count() == 0) {
            $this->status = 'done';
            DB::table('mailings')->where('id', $this->id)->update(['status'=> 'done']);
        }
        $statuses = [
            'draft' => 'Черновик',
            'pending' => 'Запланирована',
            'sent' => 'Отправлена',
            'done' => 'Завершена',
            'processing' => 'Отправляется',
            'fail' => 'Ошибка отправки',
            'cancelled' => 'Отменена',
        ];

        if ($this->pending) {
            return 'Запланирована';
        }
        return $statuses[$this->status];
    }
}
