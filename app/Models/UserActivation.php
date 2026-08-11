<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class UserActivation extends Model
{
    protected $table = 'users_activation';
    protected $guarded = ['id'];
    protected $casts = [
        'verify_phone_data' => 'array',
        'is_verified' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id')->withTrashed();
    }
}
