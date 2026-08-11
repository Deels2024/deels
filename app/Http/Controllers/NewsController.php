<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\Mailing;
use App\Models\News;
use App\Models\NewsletterMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\View\View;

class NewsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return View
     */
    public function index()
    {
        $title = trans('app.news');

        return view('admin.news', ['news' => News::query()->paginate(20), 'title' => $title]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('app.news');

        return view('admin.news_create', ['news' => News::all(), 'title' => $title]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $rules = [
            'title' => 'required',
            'text' => 'required',
        ];
        $this->validate($request, $rules);

        $create = News::create($request->except(['_token', 'files']));

        if ($create) {
            return redirect(route('news_list'));
        }

        return back()->with('error', trans('app.something_went_wrong'))->withInput($request->input());
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return View
     */
    public function edit(News $id)
    {
        return view('admin.news_edit', ['news' => $id]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return Response
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(News $id, Request $request)
    {
        $rules = [
            'title' => 'required',
            'text' => 'required',
        ];
        $this->validate($request, $rules);

        $create = $id->update($request->except(['_token', 'files']));

        if ($create) {
            return redirect(route('news_list'));
        }

        return back()->with('error', trans('app.something_went_wrong'))->withInput($request->input());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function destroy(Faq $faq, Request $request)
    {
        if (config('app.is_demo')) {
            return back()->with('error', __('app.feature_disable_demo'));
        }

        $user_id = request()->user()->id;
        $data_id = $request->data_id;
        $r = $faq::find($data_id);
        if ($r->user_id != $user_id) {
            exit(trans('app.unauthorised_access'));
        }
        $r->delete();

        return ['success' => 1];
    }

    public function mailing()
    {
        $mailings = \App\Models\Mailing::orderBy('id', 'DESC')->paginate(20);
        return view('admin.mailing')->with(['mailings' => $mailings]);
    }

    public function mailing_mails(Request $request, $id)
    {
        $mailing = \App\Models\Mailing::find($id);
        $query = $request->input('q');
        $failed = $request->input('failed');
        $pending = $request->input('pending');
        $sending = $request->input('sending');
        if (!$mailing) {
            abort(404);
        }
        $receivers_query = NewsletterMail::where('newsletter_id', $mailing->id);
        if ($query) {
            $receivers_query->where('email', $query);
        }
        if ($failed) {
            $receivers_query->where('status', 'fail');
        }
        if ($pending) {
            $receivers_query->where('status', 'pending');
        }
        if ($sending) {
            $receivers_query->where('status', 'sending');
        }



        $receivers = $receivers_query->paginate(20);
        return view('admin.mailing_mails')->with(['mailing' => $mailing, 'receivers' => $receivers]);
    }

    public function mailing_mails_show(Request $request, $id) {

        $mailing = Mailing::find($id);

        preg_match('~src="(data:image[^"]*)"~', $mailing->text, $matches);
        $mailingText = $mailing->text;
        if (isset($matches[1])) {
            [$type, $data] = explode(';', $matches[1]);
            [, $data] = explode(',', $data);
            file_put_contents(public_path('img_email.png'), base64_decode($data));
            $mailingText = str_replace([$matches[1], 'width: 558px;'], [
                'https://deels.ru/img_email.png?a=' . microtime(), 'width: 100%;',
            ], $mailing->text);
        }

        $mailingText = preg_replace('/<a (.+ )?href="([^"]+)"([^>]+)?>/', '<a ${1} href="https://deels.ru/mail_track?action=click&mail_id=' . $mailing->id . '&redirect=${2}"${3}>', $mailingText);

        $body = $mailingText;
        return view('newsletters.mail', compact('body','mailing'));

    }

    public function send_single_mail(Request $request, $id)
    {
        $mail = \App\Models\NewsletterMail::find($id);
        if (!$mail) {
            return redirect()->back()->with(['fail' => 'Ошибка']);
        }
        $mail->status = 'pending';
        $mail->save();

        return redirect()->back()->with(['success' => 'Успешно']);
    }

    public function remove_single_mail(Request $request, $id)
    {
        $mail = \App\Models\NewsletterMail::find($id);
        if (!$mail) {
            return redirect()->back()->with(['fail' => 'Ошибка']);
        }
        $mail->status = 'success';
        $mail->save();
        try {
            $user = User::where('email', $mail->email)->update(['unsubscribe' => true]);
        } catch (\Throwable $e) {

        }

        return redirect()->back()->with(['success' => 'Успешно']);
    }


    public function mailingSave(Request $request)
    {
        $users = [];
        $gmail_exclude = false;
        if (!$request->get('message')) {
            return back()->with('error', 'Укажите текст рассылки');
        }
        if ($request->get('sendCampaignUsers')) {
            $users = ['all'];
        }
        if ($request->get('specificUser')) {
            $users = explode(',', $request->get('specificUser'));
        }
        $sent_at = Carbon::now();
        if ($request->get('sendByTime') && $request->get('date')) {
            $sent_at = Carbon::parse($request->get('date'));
        }

        if ($request->get('gmail_exclude')) {
            $gmail_exclude = true;
        }

        Mailing::create([
            'subject' => $request->get('mail-theme'),
            'text' => $request->get('message'),
            'sent' => false,
            'users' => $users,
            'status' => 'pending',
            'sent_at' => $sent_at,
            'gmail_exclude' => $gmail_exclude,
            'date' => $request->get('date'),
        ]);

        return back();
    }
}
