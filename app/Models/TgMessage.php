<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TgMessage extends Model
{
    protected $table = 'tg_messages';
    protected $fillable=['chat_id','user_id','command','firstname','lastname','username','last_message','bot_message_id','lang', 'uses', 'use_at'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'telegram_id');
    }
}
