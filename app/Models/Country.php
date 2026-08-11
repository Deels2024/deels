<?php

declare(strict_types=1);

namespace App\Models;

class Country extends Model
{
    protected $fillable = ['name_ru'];
    public $timestamps = false;
}
