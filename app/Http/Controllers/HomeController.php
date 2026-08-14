<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Mail\ContactUs;
use App\Services\Home\HomePageDataService;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    /**
     * Show the application dashboard.
     */
    public function index(HomePageDataService $homePageData)
    {
        $data = $homePageData->get(auth()->user());

        if (request()->wantsJson()) {
            return response()->json($homePageData->legacyJson($data));
        }

        $view = config('homepage.use_v2') && view()->exists('home-v2') ? 'home-v2' : 'home';

        return view($view, $data);
    }

    public function contactUs()
    {
        $title = trans('app.contact_us');

        return view('contact_us', compact('title'));
    }

    public function contactUsPost(Request $request)
    {

        $rules = [
            'name' => 'required',
            'email' => 'required|email',
            'agreement' => 'required',
            'message' => 'required',
        ];
        $request->merge(["subject" => "Сообщение из контактной формы"]);
        if (1 == get_option('enable_recaptcha_contact_form')) {
//            $rules['g-recaptcha-response'] = 'required';
        }
        $validator = validator($request->all(), $rules);
        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        }

        if (1 == get_option('enable_recaptcha_contact_form')) {
//            $secret             = get_option('recaptcha_secret_key');
//            $gRecaptchaResponse = $request->input('g-recaptcha-response');
//            $remoteIp           = $request->ip();
//
//            $recaptcha = new ReCaptcha($secret);
//            $resp      = $recaptcha->verify($gRecaptchaResponse, $remoteIp);
//            if (!$resp->isSuccess()) {
//                return redirect()->back()->with('error', 'reCAPTCHA is not verified');
//            }
        }

        if (Str::contains($request->input('email'), 'godaddy') || $request->input('lastname')) {
            return redirect()->back()->with('success', trans('app.message_has_been_sent'));
        }

        try {
            Mail::send(new ContactUs($request));
//            Mail::send(new ContactUsSendToSender($request));
        } catch (Exception $exception) {
            return redirect()->back()->with('error', '<h4>' . trans('app.smtp_error_message') . '</h4>' . $exception->getMessage())->withInput();
        }

        return redirect()->back()->with('success', trans('app.message_has_been_sent'));
    }

    public function acceptCookie(Request $request)
    {
        return response(['accept_cookie' => true])->cookie('accept_cookie', true, 43800);
    }

    public function test(Request $request)
    {
        dump($request->user());
        dump(auth()->user());
        return response()->json(auth()->user());
    }

    /**
     * @return RedirectResponse|Redirector
     *
     * Clear all cache
     */
    public function clearCache()
    {
        Artisan::call('debugbar:clear');
        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        if (\function_exists('exec')) {
            exec('rm ' . storage_path('logs/*'));
        }
        $this->rrmdir(storage_path('logs/'));

        return redirect(route('home'));
    }

    private function getCampaignPopularTags(Collection $campaigns): array
    {
        $tags = [];
        foreach ($campaigns as $campaign) {
            if ($campaign->tags) {
                foreach ($campaign->tags as $tag) {
                    if (!isset($tags[$tag])) {
                        $tags[$tag] = 1;
                        continue;
                    }
                    ++$tags[$tag];
                }
            }
        }
        asort($tags);

        return \array_slice(array_keys($tags), 0, 5);
    }

    public function rrmdir($dir): void
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ('.' != $object && '..' != $object) {
                    if (is_dir($dir . '/' . $object)) {
                        $this->rrmdir($dir . '/' . $object);
                    } else {
                        unlink($dir . '/' . $object);
                    }
                }
            }
            // rmdir($dir);
        }
    }

    public function offer()
    {
        return view('offer');
    }

    public function cardPay()
    {
        return view('sber.card_pay');
    }

    public function giftOffer()
    {
        return view('sber.gift_offer');
    }

    public function accessOffer()
    {
        return view('sber.access_offer');
    }

    public function personalOffer()
    {
        return view('sber.personal_offer');
    }

    public function license()
    {
        return view('sber.license');
    }

    public function rules()
    {
        return view('sber.rules');
    }

    public function banned()
    {
        if(!auth()->user()) {
            return response()->view('errors.404', [
                'pageTitle' => 'Доступ заблокирован',
                'errorSuptitle' => 'Обнаружена подозрительная активность',
                'errorTitle' => 'Ваш IP-адрес заблокирован на 24 часа',
                'showReturnLink' => false,
            ], 403);
        }
        return view('banned');
    }


}
