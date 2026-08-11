<?php

namespace App\Http\Controllers;

use App\Jobs\FireBaseEvent;
use App\Models\Payment;
use App\Models\PaymentComment;
use App\Models\Thanks;
use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Mail;

class PaymentCommentController extends Controller
{
    public function comment(Request $request)
    {
        $validated = $request->validate([
            'payment_id' => 'required|exists:payments,id',
            'text' => 'required|string|min:20|max:140000'
        ]);

        if (Payment::find($validated['payment_id'])->campaign->user->id !== auth()->id()) {
            abort(403);
        }

        PaymentComment::create(
            $validated
        );

        return back()->with('success', 'Коммент сохранен успешно!');
    }

    public function thankList()
    {
        if (auth()->user()->is_admin()) {
            $t = Thanks::with('payment')
                ->orderByDesc('created_at')
                ->get();
        } else {
            $t = Thanks::with('payment')
                ->where('receiver', auth()->user()->email)
                ->where('approved', !auth()->user()->is_admin())
                ->where('moderated', !auth()->user()->is_admin())
                ->orderByDesc('created_at')
                ->get();
        }


        return view('admin.thanks', compact('t'));
    }

    public function thank(Payment $payment, Request $request)
    {
        $data = [
            'receiver' => $payment->email,
            'payment_id' => $payment->id,
        ];


        if($request->get('comment')) {
            $data['data'] = [
                'type' => 'comment',
                'payload' => $request->get('comment')
            ];
        } else {
            return response()->json([
                'success' => false,
                'error' => 'Напишите благодарность'
            ]);
        }


//        if ($request->file('image') || $request->file('audio')) {
//            $k = $request->file('image') ? 'image' : 'audio';
//            $file = $request->file($k);
//
//            $filename = time() . "_$payment->id." . $file->getClientOriginalExtension();
//            $file->move(
//                public_path('thanks'),
//                $filename
//            );
//
//            $data['data'] = [
//                'type' => $k,
//                'payload' => "/thanks/$filename"
//            ];
//        } else {
//            $data['data'] = [
//                'type' => 'comment',
//                'payload' => $request->get('comment')
//            ];
//        }

        Thanks::create($data);

        return back()->with(['thankToModeration' => true]);
    }

    public function moderate(Request $request)
    {
        $t = Thanks::find($request->get('thank_id'));
        $t->update([
            'approved' => $request->get('action') === 'approve',
            'moderated' => true
        ]);
      
        $t->payment->update(['status' => 'success']);

        if (($email = $t->payment->email) && $request->get('action') === 'approve') {
            \Illuminate\Support\Facades\Mail::send(
                [],
                [],
                function (\Illuminate\Mail\Message $message) use ($email): void {
                    $message
                        ->from(env('MAIL_FROM_ADDRESS', 'info@deels.ru'), 'DEELS')
                        ->to($email)
                        ->subject('Благодарность на DEELS')
                        ->html('Вам отправили благодарность за донат, посмотрите ее на https://deels.ru/dashboard/thanks', 'utf-8');
                }
            );


            $t->payment->update(['status' => 'success']);

            if($t->payment->user_id) {
                FireBaseEvent::dispatch( $t->payment->user_id, 'Вас поблагодарили за донат!', $t->payment->campaign_id, 'campaign');
            }

        }

        return response()->json(['success' => 1]);
    }
}
