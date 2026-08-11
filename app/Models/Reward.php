<?php

declare(strict_types=1);

namespace App\Models;

class Reward extends Model
{
    protected $guarded = [];
    public $timestamps = false;

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }
}
