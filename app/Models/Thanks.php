<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Thanks extends Model
{
    protected $table = 'thanks';

    protected $guarded = [];

    protected $casts = [
        'data' => 'array'
    ];


    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function getDataAttribute($value)
    {
        if(!is_array($value)) {
            $value = json_decode($value, true);

            if(!is_array($value)) {
                return  ['type' => '', 'payload' => ''];
            }
        }
        return $value;
    }
}
