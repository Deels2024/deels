<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SMSCController extends Controller
{

    public function callback(Request $request)
    {
        return response([
            'success' => true,
        ]);
    }


}
