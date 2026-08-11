<?php

declare(strict_types=1);

namespace App\Models;

class ChildComment extends Model
{
    protected $table = 'comments';

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
