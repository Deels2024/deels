<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Abuse extends Model

{

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'abuses';
    protected $guarded = ['id'];
    protected $fillable = ['user_id', 'abused_by', 'abuse', 'confirmed', 'blocked'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function abuser()
    {
        return $this->belongsTo(User::class, 'abused_by');
    }

}
