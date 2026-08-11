<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\Campaign;
use App\Models\Clickhouse\Action;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Story;
use App\Models\WithdrawalRequest;
use App\User;
use Bavix\Wallet\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class LogsController extends Controller
{
    public function index(Request $request)
    {
        $logs = DB::table('logs')->paginate(15);
        $title = 'Логи';
        return view('admin.logs', compact('logs', 'title'));
    }

}
