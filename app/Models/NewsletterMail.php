<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\NewsletterMail
 *
 * @property int $id
 * @property int|null $newsletter_id
 * @property int|null $subscriber_id
 * @property string|null $token
 * @property string|null $email
 * @property string $status
 * @property string|null $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Newsletter|null $newsletter
 * @method static \Illuminate\Database\Eloquent\Builder|NewsletterMail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NewsletterMail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NewsletterMail query()
 * @method static \Illuminate\Database\Eloquent\Builder|NewsletterMail whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NewsletterMail whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NewsletterMail whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NewsletterMail whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NewsletterMail whereNewsletterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NewsletterMail whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NewsletterMail whereSubscriberId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NewsletterMail whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NewsletterMail whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class NewsletterMail extends Model
{
    protected $table = 'newsletter_mails';
    protected $guarded = ['id'];

    public function newsletter()
    {
        return $this->belongsTo(Mailing::class);
    }

    public function getStatusColor() {
        $statuses = [
            'pending' => 'light',
            'sending' => 'warning',
            'success' => 'success',
            'fail' => 'danger',
        ];
        return $statuses[$this->status];
    }

    public function getStatus()
    {
        $statuses = [
            'pending' => 'Ожидает обработку',
            'sending' => 'В очереди',
            'success' => 'Отправлено',
            'fail' => 'Ошибка',
        ];

        return $statuses[$this->status];
    }
}
