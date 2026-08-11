<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model

{

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'orders';
    protected $guarded = ['id'];

    // Новый
    const STATUS_NEW = 0;
    // Оплачен
    const STATUS_DONE = 1;
    // Отменен
    const STATUS_CANCEL = 2;

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}
