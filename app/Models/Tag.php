<?php

declare(strict_types=1);

namespace App\Models;

class Tag extends Model
{
    protected $table = 'tags';

    protected $guarded = [];

    public function stories()
    {
        return $this->belongsToMany(Story::class, 'story_tag', 'tag_id');
    }
}
