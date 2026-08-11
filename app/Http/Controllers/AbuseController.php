<?php

namespace App\Http\Controllers;

use App\Models\Abuse;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AbuseController extends Controller
{
    public function abuse(Request $request)
    {
        $user_id = $request->input('user_id');
        $abuser_id = Auth::user()->id ?? auth()->user()->id ?? $request->input('abuser_id');
        $abuse_text = $request->input('abuse');
        $blocked = $request->input('blocked') ?? 0;

        try {
            Abuse::updateOrCreate(
                ['user_id' => $user_id, 'abused_by' => $abuser_id],
                [
                    'abuse' => $abuse_text,
                    'blocked' => $blocked,
                ]
            );
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Ваша жалоба получена!',
        ]);
    }

    public function get_abuse(Request $request)
    {
        $user_id = $request->input('user_id');
        $abuser_id = Auth::user()->id ?? auth()->user()->id ?? $request->input('abuser_id');

        $abuse = Abuse::where('abused_by', $abuser_id)->where('user_id', $user_id)->first();
        if($abuse) {
            return response()->json([
                'success' => true,
                'user_id' => $abuse->user_id,
                'abused_by' => $abuse->abused_by,
                'blocked' => $abuse->blocked,
                'abuse' => $abuse->abuse,
            ]);
        }
        return response()->json([
            'success' => false,
        ]);
    }



    public function abuses_list(Request $request) {
        $abuses_query = Abuse::query();
        $type = $request->input('type');
        $user_id = $request->input('user_id');
        $abuser_id= $request->input('abuser_id');
        $title = 'Жалобы';

        if($type && $type == 'confirmed') {
            $abuses_query->where('confirmed', 1);
            $title = 'Жалобы одобренные';
        } else {
            $abuses_query->where('confirmed', 0);
        }

        if($user_id) {
            $abuses_query->where('user_id', $user_id);
        }
        if($abuser_id) {
            $abuses_query->where('abused_by', $abuser_id);
        }


        $abuses = $abuses_query->paginate(20);

        return view('admin.abuses', compact('title', 'abuses'));
    }

    public function abuses_list_action(Request $request) {
        $abuse = Abuse::find($request->input('abuse_id'));
        if($request->input('type') == 'approved') {
            $abuse->confirmed = true;
            $abuse->save();
        }
        if($request->input('type') == 'declined') {
            $abuse->delete();
        }

        return  back()->with('success','Действие успешно');

    }


}