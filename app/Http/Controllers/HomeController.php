<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Jobs\FinishChallenge;
use App\Mail\ContactUs;
use App\Models\Campaign;
use App\Models\Challenge;
use App\Models\Comment;
use App\Models\Payment;
use App\Models\Story;
use App\Models\User;
use App\Repositories\CampaignRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    /**
     * Show the application dashboard.
     */
    public function index(CampaignRepository $campaignRepository)
    {

        $title = 'Deels платформа для творчества и продвижения контента - Заработок онлайн на сторис и челленджах';
        $description = 'Deels.ru  |  Платформа для творчества и продвижения контента | Заработок на сторис  | Участие в челленджах  |  Растущие сообщество талантливых создателей и энтузиастов';
        $user = Auth::user();

        if($user) {

        } else {
//            return view(
//                'auth.login',
//                compact(
//                    'title',
//                    'description',
//
//                )
//            );
        }

//        $completedCampaigns = $campaignRepository->randomFullyDonatedCampaigns(4);
        $completedCampaigns = Cache::remember('home.hero.completed_campaigns', now()->addMinutes(30), static function () {
            return Campaign::whereIn('slug', [
                'kreslo-gamak',
                'bilet-na-koncert-mukki',
                'na-kraski-dlia-novoi-kartiny',
                'na-obucenie-tatu-mastera',
                'donat-v-frostborn',
                'buket-romasek',
                'dorado',
                'obucenie-52',
            ])->get();
        });
        $today = Carbon::now()->format('Y-m-d');
        $week = Carbon::now()->subDays(7)->format('Y-m-d');
        $last_week = Carbon::now()->subDays(14)->format('Y-m-d');
        $storiesBlocks = Cache::remember('home.blocks.stories', now()->addMinutes(5), function () use ($week, $last_week, $today) {
            $newStories = Story::with('comments', 'likes')
                ->where('active', true)
                ->where('declined', false)
                ->where('created_at', '>=', $week . ' 00:00:00')
                ->where('created_at', '<=', $today . ' 23:59:59')
                ->orderBy('created_at', 'DESC')
                ->take(10)
                ->get();

            if (count($newStories) == 0) {
                $newStories = Story::with('comments', 'likes')
                    ->where('active', true)
                    ->where('declined', false)
                    ->where('created_at', '>=', $last_week . ' 00:00:00')
                    ->where('created_at', '<=', $today . ' 23:59:59')
                    ->orderBy('created_at', 'DESC')
                    ->take(10)
                    ->get();
            }

            $donateStories = Story::with('comments', 'likes')
                ->where('active', true)
                ->where('declined', false)
                ->where('amount', '>', 0)
                ->where('paid', true)
                ->inRandomOrder()
                ->take(10)
                ->get();

            $topStories = Story::with('comments', 'likes')
                ->where('active', true)
                ->where('declined', false)
                ->where('created_at', '>=', $today . ' 00:00:00')
                ->where('created_at', '<=', $today . ' 23:59:59')
                ->withCount('comments', 'likes')
                ->orderBy('comments_count', 'desc')
                ->orderBy('likes_count', 'desc')
                ->orderBy('created_at', 'DESC')
                ->take(10)
                ->get();

            if (count($topStories) < 6) {
                $topStories = Story::with('comments', 'likes')
                    ->where('active', true)
                    ->where('declined', false)
                    ->where('created_at', '>=', $week . ' 00:00:00')
                    ->where('created_at', '<=', $today . ' 23:59:59')
                    ->withCount('comments', 'likes')
                    ->havingRaw('comments_count > 0 OR likes_count > 0')
                    ->orderBy('comments_count', 'desc')
                    ->orderBy('likes_count', 'desc')
                    ->orderBy('created_at', 'DESC')
                    ->take(10)
                    ->get();
            }
            if (count($topStories) < 6) {
                $topStories = Story::with('comments', 'likes')
                    ->where('active', true)
                    ->where('declined', false)
                    ->where('created_at', '>=', $last_week . ' 00:00:00')
                    ->where('created_at', '<=', $today . ' 23:59:59')
                    ->withCount('comments', 'likes')
                    ->havingRaw('comments_count > 0 OR likes_count > 0')
                    ->orderBy('comments_count', 'desc')
                    ->orderBy('likes_count', 'desc')
                    ->orderBy('created_at', 'DESC')
                    ->take(10)
                    ->get();
            }
            if (count($topStories) < 6) {
                $topStories = Story::with('comments', 'likes')
                    ->where('active', true)
                    ->where('declined', false)
                    ->withCount('comments', 'likes')
                    ->havingRaw('comments_count > 0 OR likes_count > 0')
                    ->orderBy('comments_count', 'desc')
                    ->orderBy('likes_count', 'desc')
                    ->orderBy('created_at', 'DESC')
                    ->take(10)
                    ->get();
            }

            return compact('newStories', 'donateStories', 'topStories');
        });
        $viewer = Auth::user() ?? auth()->user();
        $visibility = app(\App\Services\Contests\ContestVisibilityService::class);
        $visibleStories = static function ($stories) use ($viewer, $visibility) {
            return $stories->filter(static function (Story $story) use ($viewer, $visibility): bool {
                $contest = $story->challenge_id
                    ? $story->challenge
                    : ($story->battle_id ? $story->battle : null);

                return !$contest || $visibility->canView($contest, $viewer);
            })->values();
        };
        $newStories = $visibleStories($storiesBlocks['newStories']);
        $donateStories = $visibleStories($storiesBlocks['donateStories']);
        $topStories = $visibleStories($storiesBlocks['topStories']);

        $topChallenges = Cache::remember(
            'home.blocks.top_challenges:' . ($viewer?->id ?? 'guest'),
            now()->addMinutes(5),
            static function () use ($viewer) {
            $query = Challenge::where('challenges.active', 1)
                ->where('challenges.declined', 0)
                ->whereNull('challenges.blocked_at')
                ->whereNull('finished_at');
            app(\App\Services\Contests\ContestVisibilityService::class)
                ->applyToContests($query, 'challenges', $viewer);

            return $query
                ->withCount('comments')
                ->withCount('likes')
                ->withCount('views')
                ->inRandomOrder()
                ->orderBy('views_count', 'DESC')
                ->orderBy('likes_count', 'DESC')
                ->orderBy('comments_count', 'DESC')
                ->take(10)
                ->get();
        });

        $stats = Cache::remember('home.blocks.stats', now()->addMinutes(5), static function () {
            $campaignsCount = Campaign::active()->count();
            $usersCount = User::count();
            $fundRaised = Payment::whereStatus('success')->sum('amount');
            $fundedCampaignsCount = Campaign::join('payments', 'campaign_id', 'campaigns.id')
                ->where('payments.status', 'success')
                ->count();

            $storiesCount = Story::active()->count();
            $storiesDonatedCount = DB::table('transactions')->where('meta', 'like', '%{"get":"story"%')->sum('amount');
            $storiesCommentsCount = Comment::whereNotNull('story_id')->where('approved', true)->count();
            $storiesViewsCount = \App\Models\View::count();

            return compact(
                'campaignsCount',
                'usersCount',
                'fundRaised',
                'fundedCampaignsCount',
                'storiesCount',
                'storiesDonatedCount',
                'storiesCommentsCount',
                'storiesViewsCount'
            );
        });
        extract($stats, EXTR_OVERWRITE);

        $fundedCampaigns = $campaignRepository->fundedCampaigns(8);
        $newCampaigns = $campaignRepository->newCampaigns(8, $fundedCampaigns->pluck('id'));
        $latestFundedCampaigns = $campaignRepository->latestFundedCampaigns(8);

        if (request()->wantsJson()) {
            return response()->json([
                'title' => $title,
                'description' => $description,
                'newCampaigns' => $newCampaigns,
                'fundedCampaigns' => $fundedCampaigns,
                'campaignsCount' => $campaignsCount,
                'usersCount' => $usersCount,
                'fundRaised' => $fundRaised,
                'latestFundedCampaigns' => $latestFundedCampaigns,
                'fundedCampaignsCount' => $fundedCampaignsCount,
                'completedCampaigns' => $completedCampaigns,
                'storiesCount' => $storiesCount,
                'storiesDonatedCount' => $storiesDonatedCount,
                'storiesCommentsCount' => $storiesCommentsCount,
                'storiesViewsCount' => $storiesViewsCount,
                'topDonaters' => [],
                'topReferrals' => [],
                'topWinners' => [],
                'doneCampaigns' => [],
                'topChallenges' => $topChallenges,
            ]);
        } else {
            return view(
                'home',
                compact(
                    'title',
                    'description',
                    'completedCampaigns',
                    'newStories',
                    'fundedCampaigns',
                    'newCampaigns',
                    'latestFundedCampaigns',
                    'donateStories',
                    'topStories',
                    'campaignsCount',
                    'usersCount',
                    'fundRaised',
                    'fundedCampaignsCount',
                    'storiesCount',
                    'storiesDonatedCount',
                    'storiesCommentsCount',
                    'storiesViewsCount',
                    'topChallenges',
                )
            );
        }
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
