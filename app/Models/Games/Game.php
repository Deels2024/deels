<?php

namespace App\Models\Games;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Game extends Model

{
    protected $table = 'games';
    protected $guarded = ['id'];

    protected $casts = [
        'settings' => 'array',
    ];
}
