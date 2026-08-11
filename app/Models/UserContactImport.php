<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserContactImport extends Model
{
    protected $guarded = [];

    protected $casts = [
        'first_confirmed_at' => 'datetime',
        'last_denied_at' => 'datetime',
        'next_prompt_at' => 'datetime',
    ];
}
