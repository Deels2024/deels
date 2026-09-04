<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Http\Requests\CampaignStoreRequest;
use App\Jobs\FireBaseEvent;
use App\Models\Campaign;
use App\Models\Category;
use App\Models\Country;
use App\Models\Likes;
use App\Models\Payment;
use App\Models\PendingReferrals;
use App\Models\Reward;
use App\Models\Thanks;
use App\Models\User;
use App\Services\CampaignFilterService;
use App\Services\ReferralBonusService;
use App\Utils\SEOFriendlyPaginator;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Mail\Message;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use NextApps\VerificationCode\VerificationCode;
use Stripe\Charge;
use Stripe\Error\Card;
use Stripe\Stripe;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use YandexCheckout\Client;

/**
 * Class CampaignsController.
 */
class CampaignsController extends Controller
{
    public function index(
        Request               $request,
        CampaignFilterService $campaignFilterService,
        $slug = null
    )
    {
        $category = null;
        if($slug) {
            $category = Category::where('slug', $slug)->first();
            if($category) {
                $request->merge(["category"=>$category->id]);
            }
        }

        $type = $request->input('type');
        if (!$type) {
            $request->merge(["type" => "funded"]);
        }

        $campaignsQuery = $campaignFilterService
            ->getFilteredCampaigns($request->all())
            ->orderByDesc('created_at');

        if($category) {
            $filteredCategory = $category;
        } else {
            $filteredCategory = $request->get('category') ? Category::find($request->get('category')) : null;
        }

        if ($request->wantsJson()) {
            $page = max(1, (int)$request->input('page', 1));
            $cacheKey = 'api.campaigns.index:' . md5(json_encode([
                'query' => $request->query(),
                'slug' => $slug,
                'page' => $page,
            ]));

            $campaigns = Cache::remember($cacheKey, now()->addMinutes(3), static function () use ($campaignsQuery) {
                return $campaignsQuery->paginate(20);
            });

            return response()->json([
                'campaigns' => $campaigns,
                'filteredCategory' => $filteredCategory,
            ]);
        } else {
            $page = max(1, (int)$request->input('page', 1));
            $campaigns = $campaignsQuery->paginate(20, ['*'], 'page', $page);
            $categories = Cache::remember('categories', 500, static function () {
                return Category::withCount('campaigns')->get();
            });

            return view(
                'browse_campaigns',
                compact('campaigns', 'filteredCategory', 'categories'));
        }
    }

    public function create(): Factory|View|Application
    {
        $title = trans('app.start_a_campaign');

        return view('campaigns.create', compact('title'));
    }

    public function store(CampaignStoreRequest $request)
    {
        $slug = unique_slug($request->title);

        $image = '';
        $images = [];
        $files = $request->file('files') ?? [];
        $code = $request->input('code');
        $skip = $request->input('skip');
        if (!$skip) {
//            $verification = VerificationCode::verify($code, Auth::user()->email, true);
//            if (!$verification) {
//                if ($request->wantsJson()) {
//                    return response()->json([
//                        'success' => false,
//                        'error' => 'Неправильный код подтверждения',
//                    ]);
//                }
//                return redirect()->back()->with('error', 'Неправильный код подтверждения!')->withInput($request->input());
//            }
        }
        if (intval($request->goal) <= 0) {
            return redirect()->back()->with('error', 'Укажите размер цели больше 0')->withInput($request->input());
        }

        array_unshift($files, $request->file('mainImg'));
        $request->files->add(['files' => $files]);

        if ($request->files->has('files')) {
            $storedData = (new MediaController())->store($request);
            if (isset($storedData['images'])) {
                $images = array_column($storedData['images'], 'id');
            }
            $image = array_shift($images);
        }

        // feature image has been moved to update
        $data = [
            'user_id' => request()->user()->id ?? auth()->id() ?? null,
            'category_id' => $request->category,
            'title' => $request->title,
            'slug' => $slug,
            'short_description' => $request->short_description,
            'description' => $request->description,
            'campaign_owner_commission' => get_option('campaign_owner_commission'),
            'goal' => intval($request->goal),
            'min_amount' => $request->min_amount,
            'max_amount' => $request->max_amount,
            'recommended_amount' => $request->recommended_amount,
            'amount_prefilled' => $request->amount_prefilled,
            'end_method' => $request->end_method,
            'video' => $request->video,
            'feature_image' => $image,
            'images' => $images,
            'status' => 0,
            'country_id' => $request->country_id,
            'address' => $request->address,
            'is_funded' => 0,
            'is_feature' => 0,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'moderated' => 0,
        ];

        if (!$data['feature_image']) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Отсутствует обложка копилки',
                ]);
            }

            return redirect()->back()->with('error', 'Invalid img');
        }

        $create = Campaign::create($data);

        if ($create) {
            try {
                Mail::send(
                    [],
                    [],
                    function (Message $message) use ($create): void {
                        $message
                            ->from(env('MAIL_FROM_ADDRESS', 'info@deels.ru'), 'DEELS')
                            ->to($create->user->email)
                            ->subject('Копилка успешно создана')
                            ->html('<p>Ваша копилка на DEELS успешно создана!</p> <p>Поскорее делитесь ею в соцсетях, чтобы как можно больше спонсоров увидело Вашу мечту!</p>');
                    }
                );
            } catch (\Throwable $e) {

            }


            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                ]);
            }

            return redirect(route('dashboard'))->with('success', trans('app.campaign_created'));
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => false,
            ]);
        }

        return back()->with('error', trans('app.something_went_wrong'))->withInput($request->input());
    }

    /** @return Application|Factory|View */
    public function myCampaigns()
    {
        $title = trans('app.my_campaigns');
        $user = request()->user();
        // $my_campaigns = $user->my_campaigns;
        $my_campaigns = Campaign::whereUserId($user->id)->orderBy('created_at', 'desc')->get();

        return view('admin.my_campaigns', compact('title', 'my_campaigns'));
    }

    /** @return Application|Factory|View */
    public function myPendingCampaigns()
    {
        $title = trans('app.pending_campaigns');
        $user = request()->user();
        $my_campaigns = Campaign::pending()->whereUserId($user->id)->orderBy('created_at', 'desc')->get();

        return view('admin.my_campaigns', compact('title', 'my_campaigns'));
    }

    /** @return Application|Factory|View|BinaryFileResponse */
    public function allCampaigns()
    {
        $title = trans('app.all_campaigns');
        $campaigns = Campaign::active()
            ->withCount('likes')
            ->orderByDesc('slider_order')
            ->orderByDesc('id');

        if (request()->has('excel')) {
            return $this->collectionToExcel($campaigns->get());
        }

        $campaigns = $campaigns->paginate(20);

        return view('admin.all_campaigns', compact('title', 'campaigns'));
    }

    public function campaignsToModerate()
    {
        $title = trans('app.moderate_campaigns');
        $campaigns = Campaign::where('moderated', false)->orderBy('created_at', 'asc')->paginate(20);

        return view('admin.all_campaigns', compact('title', 'campaigns'));
    }

    /** @return Application|Factory|View|BinaryFileResponse */
    public function staffPicksCampaigns()
    {
        $title = trans('app.staff_picks');
        $campaigns = Campaign::staff_picks()->orderBy('created_at', 'desc')->paginate(20);
        if (request()->has('excel')) {
            return $this->collectionToExcel(Campaign::staff_picks()->orderBy('created_at', 'desc')->get());
        }

        return view('admin.all_campaigns', compact('title', 'campaigns'));
    }

    /** @return Application|Factory|View|BinaryFileResponse */
    public function fundedCampaigns()
    {
        $title = trans('app.full_funded');
        $campaigns = Campaign::query()
            ->whereRaw('goal<=(SELECT SUM(amount) FROM payments WHERE campaign_id=campaigns.id and status="success")')
            ->orderBy('created_at', 'desc');

        if (request()->has('excel')) {
            return $this->collectionToExcel($campaigns->get());
        }

        $campaigns = $campaigns->paginate(20);

        return view('admin.all_campaigns', compact('title', 'campaigns'));
    }

    /** @return Application|Factory|View|BinaryFileResponse */
    public function blockedCampaigns()
    {
        $title = trans('app.blocked_campaigns');
        $campaigns = Campaign::blocked()->orderBy('created_at', 'desc')->paginate(20);
        if (request()->has('excel')) {
            return $this->collectionToExcel(Campaign::blocked()->orderBy('created_at', 'desc')->get());
        }
        $show_ai_moderated = true;

        return view('admin.all_campaigns', compact('title', 'campaigns', 'show_ai_moderated'));
    }

    /** @return Application|Factory|View|BinaryFileResponse */
    public function pendingCampaigns()
    {
        $title = trans('app.pending_campaigns');
        $campaigns = Campaign::pending()->orderBy('created_at', 'desc')->paginate(20);
        if (request()->has('excel')) {
            return $this->collectionToExcel(Campaign::pending()->orderBy('created_at', 'desc')->get());
        }

        return view('admin.all_campaigns', compact('title', 'campaigns'));
    }

    /** @return Application|Factory|View|BinaryFileResponse */
    public function expiredCampaigns()
    {
        $title = trans('app.expired_campaigns');
        $campaigns = Campaign::active()->expired()->orderBy('created_at', 'desc')->paginate(20);
        if (request()->has('excel')) {
            return $this->collectionToExcel(Campaign::active()->expired()->orderBy('created_at', 'desc')->get());
        }

        return view('admin.all_campaigns', compact('title', 'campaigns'));
    }

    /** @return Application|Factory|View|BinaryFileResponse */
    public function searchAdminCampaigns(Request $request)
    {
        $title = trans('app.campaigns_search_results');
        $campaigns = Campaign::orderBy('created_at', 'desc');

        if ($from = $request->get('sumFrom')) {
            $campaigns = $campaigns->where('goal', '>=', $from);
        }

        if ($to = $request->get('sumTo')) {
            $campaigns = $campaigns->where('goal', '<=', $to);
        }

        if ($from = $request->get('date_from')) {
            $campaigns = $campaigns->where('created_at', '>=', $from);
        }

        if ($to = $request->get('date_to')) {
            $campaigns = $campaigns->where('created_at', '<=', $to);
        }

        if ($request->has('paidFrom')) {
            $campaigns = $campaigns->whereHas('payments', function ($q): void {
                $q->where('amount', '>', 1)
                    ->whereStatus('success');
            });
        }

        if ($q = $request->get('q')) {
            $campaigns = $campaigns->where(function ($builder) use ($q): void {
                $builder->where('id', $q)
                    ->orWhere('title', 'like', "%$q%");
            });
        }

        if ($to = $request->get('sumTo')) {
            $campaigns = $campaigns->where('goal', '>=', $to);
        }

        if ($request->has('ai_moderated')) {
            $campaigns = $campaigns->where('ai_moderated', true)->where('status', 3);
        }

        if (request()->has('excel')) {
            return $this->collectionToExcel($campaigns->get());
        }

        if ($user = $request->get('user')) {
            $campaigns = $campaigns->where('user_id', $user)->paginate(200);
        } else {
            $campaigns = $campaigns->paginate(20);
        }

        return view('admin.all_campaigns', compact('title', 'campaigns'));
    }

    /**
     * @param int $id
     *
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function deleteCampaigns($id = 0)
    {
        if (config('app.is_demo')) {
            return redirect()->back()->with('error', __('app.feature_disable_demo'));
        }

        if ($id) {
            $campaign = Campaign::find($id);
            if ($campaign) {
                $campaign->delete();
            }
        }

        return back()->with('success', trans('app.campaign_deleted'));
    }

    public function browseCampaignsFilter(
        Request  $request,
        Campaign $campaign
    )
    {
        if ('fully_donated' === $request->get('type')) {
            $campaign = $campaign->fullyDonated();
        }

        $campaignsBuilder = $campaign->active()
            ->with('user', 'feature_media')
            ->withCount('success_payments')
            ->withSum('success_payments', 'amount');

        if ($request->get('days_left')) {
            $today = Carbon::now();
            $from = $today->addDays($request->get('days_left')[0])->format('Y-m-d');
            $to = $today->addDays($request->get('days_left')[1])->format('Y-m-d');

            $campaignsBuilder->where(
                static function (Builder $builder) use ($from, $to): void {
                    $builder
                        ->whereBetween('end_date', [$from, $to])
                        ->orWhereNull('end_date');
                }
            );
        }

        if ($request->get('category')) {
            $campaignsBuilder->where('category_id', $request->get('category'));
        }

        if ('big' === $request->get('type')) {
            $campaignsBuilder->where('goal', '>=', 100000);
        }

        if ('funded' === $request->get('type')) {
            $campaignsBuilder->has('success_payments');
        }

        $title = __('app.browse_campaigns');

        $page = max(1, (int)$request->input('page', 1));
        $cacheKey = 'campaigns.filter:' . md5(json_encode([
            'type' => $request->get('type'),
            'days_left' => $request->get('days_left'),
            'category' => $request->get('category'),
            'page' => $page,
        ]));

        $campaigns = Cache::remember(
            $cacheKey,
            now()->addSeconds(90),
            static function () use ($campaignsBuilder) {
                return $campaignsBuilder
                    ->orderBy('created_at', 'desc')
                    ->paginate(20);
            }
        );

        return view('browse_campaigns_content', compact('title', 'campaigns'));
    }

    /** @param Request $request */
    public function addLike(Request $request)
    {
        $user_id = $request->user_id ?? Auth::id();
        $campaign_id = $request->campaign_id ?? $request->campaignId;
        $user = User::find($request->user_id);
        if (!$user) {
            return response()->json([
                'success' => false,
                'errors' => 'Пользователь не найден'
            ]);
        }
        $campaign = Campaign::find($request->campaign_id);
        if (!$campaign) {
            return response()->json([
                'success' => false,
                'errors' => 'Копилка не найдена'
            ]);
        }
        $like = Likes::where('user_id', $user_id)->where('campaign_id', $campaign_id)->first();
        if ($like) {
            $like->delete();
            return response()->json([
                'success' => true,
                'errors' => 'Лайк убран'
            ]);
        } else {
            Likes::create([
                'user_id' => $user_id,
                'campaign_id' => $campaign_id,
                'ip_address' => request()->ip() ?? null,
            ]);
            return response()->json([
                'success' => true,
                'errors' => 'Лайк добавлен'
            ]);
        }

    }

    /** @return Application|Factory|View */
    public function projectsWeLoved(): Factory|View|Application
    {
        $title = trans('app.staff_picks');
        $filteredCategory = null;
        $categories = Category::withCount('campaigns')->get();
        $page = max(1, (int)request('page', 1));
        $cacheKey = "campaigns.staff_picks:{$page}";
        $campaigns = Cache::remember(
            $cacheKey,
            now()->addMinutes(5),
            static function () {
                return Campaign::staff_picks()
                    ->active()
                    ->with('user', 'feature_media')
                    ->withCount('success_payments')
                    ->withSum('success_payments', 'amount')
                    ->orderBy('created_at', 'desc')
                    ->paginate(32);
            }
        );

        return view('browse_campaigns', compact('title', 'campaigns', 'categories', 'filteredCategory'));
    }

    public function recentlyFundedCampaigns(): Factory|View|Application
    {
        $title = trans('app.recently_funded_campaigns');
        $filteredCategory = null;
        $categories = Category::withCount('campaigns')->get();
        $page = max(1, (int)request('page', 1));
        $cacheKey = "campaigns.recently_funded:{$page}";
        $campaigns = Cache::remember(
            $cacheKey,
            now()->addMinutes(5),
            static function () {
                return Campaign::funded()
                    ->active()
                    ->with('user', 'feature_media')
                    ->withCount('success_payments')
                    ->withSum('success_payments', 'amount')
                    ->orderBy('created_at', 'desc')
                    ->paginate(32);
            }
        );

        return view('browse_campaigns', compact('title', 'campaigns', 'categories', 'filteredCategory'));
    }

    public function show($slug = null)
    {
        $campaign = Campaign::whereSlug($slug)
            ->with('success_payments.user', 'success_payments.comment', 'user', 'feature_media', 'get_category')
            ->withSum('success_payments', 'amount')
            ->firstOrFail();


        if ($campaign->status != 1) {
            if (Auth::user() && $campaign->user_id == Auth::user()->id || Auth::user() && Auth::user()->is_admin()) {

            } else {
                abort(404);
            }
        }

        $title = $campaign->title;

        if (request('test')) {
            $title = $campaign->meta_title ?: str_replace(
                ['%CATEGORY%', '%CAMPAIGN%'],
                [$campaign->get_category->category_name, $campaign->title],
                $campaign->get_category->meta_title
            );
        }

        $enable_discuss = get_option('enable_disqus_comment');

        if (\request()->wantsJson()) {
            return \response()->json([
                'campaign' => $campaign,
                'title' => $title,
                'enable_discuss' => $enable_discuss,
            ]);
        } else {
            $campaign->loadMissing([
                'success_payments.thanks.payment',
                'stories' => static function ($stories): void {
                    $stories->active()
                        ->with(['media', 'user'])
                        ->withCount(['comments', 'likes', 'views'])
                        ->orderByDesc('created_at');
                },
            ]);

            return view('campaign_single_new', compact('campaign', 'title', 'enable_discuss'));
        }
    }

    /**
     * @param $id
     *
     * @return Application|Factory|RedirectResponse|Redirector|View
     */
    public function edit($id)
    {
        $user_id = request()->user()->id;
        $campaign = Campaign::find($id);
        if ($campaign->is_ended() && !Auth::user()->is_admin()) {
            return redirect(route('all_campaigns'));
        }
        // todo: checked if admin then he can access...
        if ($campaign->user_id !== $user_id && !Auth::user()->is_admin()) {
            abort(404, __('app.unauthorised_access'));
        }

        $title = trans('app.edit_campaign');
        $countries = Country::orderBy('name', 'asc')->get();

        return view('campaigns.edit', compact('title', 'countries', 'campaign'));
    }

    public function delete_campaign($id) {
        $user_id = request()->user()->id;
        $campaign = Campaign::find($id);
        if ($campaign->user_id !== $user_id && !Auth::user()->is_admin()) {
            abort(404, __('app.unauthorised_access'));
        }
        $campaign->delete();
        return redirect()->route('my_campaigns')->with(['success' => 'Копилка удалена']);
    }

    /**
     * @param $id
     *
     * @return Application|RedirectResponse|Redirector
     *
     * @throws ValidationException
     */
    public function update(Request $request, $id)
    {
        $campaign = Campaign::whereId($id)->first();
        if ($campaign->is_ended() && !Auth::user()->is_admin()) {
            return redirect(route('all_campaigns'));
        }
        if (!$request->file('mainImg') && !$campaign->feature_image) {
            return redirect()->back()->with('error', 'Invalid img');
        }

        if($request->input('re_moderation')) {
            $campaign->status = 0;
            $campaign->saveQuietly();
        }

        $rules = [
            'category' => 'required',
            'title' => 'required',
            //            'short_description' => 'required|max:200',
            'description' => 'required',
            'goal' => 'required',
            //            'country_id'        => 'required',
            'add_to_goal' => 'gte:0',
        ];

        $this->validate($request, $rules);

        $data = [
            'category_id' => $request->category,
            'title' => $request->title,
            'short_description' => $request->short_description,
            'description' => $request->description,
            'goal' => $request->goal,
            'min_amount' => $request->min_amount,
            'max_amount' => $request->max_amount,
            'recommended_amount' => $request->recommended_amount,
            'amount_prefilled' => $request->amount_prefilled,
            'end_method' => $request->end_method,
            'video' => $request->video,
            'country_id' => $request->country_id,
            'address' => $request->address,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'tags' => $request->tags,
            'meta_title' => $request->meta_title,
            'meta_description' => $request->meta_description,
        ];

        if($request->status) {
            $data['status'] = $request->status;
        }

        if ($request->hasFile('files') || $request->hasFile('mainImg')) {
            $files = $request->file('files') ?? [];
            if ($request->hasFile('mainImg')) {
                array_unshift($files, $request->file('mainImg'));
            }
            $request->files->add(['files' => $files]);
            $images = array_column((new MediaController())->store($request)['images'], 'id');
            if ($request->hasFile('mainImg')) {
                $image = array_shift($images);
                $data['feature_image'] = $image;
            }

            $data['images'] = $images;
        }

        if (!Auth::user()->is_admin()) {
            $data['status'] = 0;
        }
        $data['is_edited'] = true;
        $data['moderated'] = Auth::user()->is_campaign_admin();

        if ($request->add_to_goal) {
            Payment::create([
                'name' => Auth::user()->name,
                'email' => Auth::user()->email,
                'campaign_id' => $id,
                'user_id' => Auth::id(),
                'amount' => $request->add_to_goal,
                'payment_method' => 'by_admin',
                'status' => 'success',
            ]);
        }

        $update = $campaign->update($data);

        if($campaign->status == 0 || $request->input('re_moderation')) {
            $campaign->status = 3;
            $campaign->save();
        }

        if ($update) {
            return redirect(route('edit_campaign', $id))->with('updated', trans('app.campaign_created'));
        }

        return back()->with('error', trans('app.something_went_wrong'))->withInput($request->input());
    }

    public function updateImage(Request $request, $id)
    {
        $request->validate(['image' => 'required|max:10000']);

        $campaign = Campaign::whereId($id)->first();
        if ($campaign->is_ended() && !Auth::user()->is_admin()) {
            return redirect(route('all_campaigns'));
        }

        if ($request->hasFile('files') || $request->hasFile('image')) {
            $images = array_column((new MediaController())->store($request)['images'], 'id');

            $data['images'] = array_merge(($campaign->images ?: []), $images);
        }

        if (!Auth::user()->is_admin()) {
            $data['status'] = 0;
        }
        $data['is_edited'] = true;
        $data['moderated'] = Auth::user()->is_campaign_admin();

        $campaign->update($data);

        return \response()->json(['success' => 1]);
    }

    public function wakeUp($id): RedirectResponse
    {
        $campaign = Campaign::findOrFail($id);

        if ($campaign->user_id !== Auth::id() && !Auth::user()->is_admin()) {
            abort(404, __('app.unauthorised_access'));
        }

        if ((int)$campaign->status !== 4) {
            return back();
        }

        if ($campaign->is_edited) {
            $campaign->status = 3;
            $campaign->moderated = false;
            $campaign->save();

            return back()->with('wake_message', 'Копилка отправлена на модерацию');
        }

        if ((int) $campaign->health > Campaign::HEALTH_MIN) {
            $campaign->status = 1;
            $campaign->save();

            return back()->with('wake_message', 'Ваша активность разбудила копилку! Продолжайте в том же духе и накопите на мечту!');
        }

        $campaign->status = 4;
        $campaign->save();

        return back()->with('wake_message', 'Нам очень жаль. Мы не смогли разбудить вашу копилку. Публикуйте сторис, участвуйте в челленджах или пригласите друзей на Deels и обязательно сможете разбудить вашу копилку!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
    }

    /**
     * @param $slug
     *
     * @return Application|Factory|View
     */
    public function showBackers($slug)
    {
        if (!$slug) {
            abort(404);
        }
        $campaign = Campaign::whereSlug($slug)->first();
        $title = trans('app.backers') . ' | ' . $campaign->title;

        return view('campaign_backers', compact('campaign', 'title'));
    }

    /**
     * @param $slug
     *
     * @return Application|Factory|View
     */
    public function showUpdates($slug)
    {
        if (!$slug) {
            abort(404);
        }
        $campaign = Campaign::whereSlug($slug)->first();

        $title = $campaign->title;

        return view('campaign_update', compact('campaign', 'title'));
    }

    /**
     * @param $slug
     *
     * @return Application|Factory|View
     */
    public function showFaqs($slug)
    {
        if (!$slug) {
            abort(404);
        }
        $campaign = Campaign::whereSlug($slug)->first();
        $title = $campaign->title;

        return view('campaign_faqs', compact('campaign', 'title'));
    }

    /**
     * @param $id
     *
     * @return mixed
     *
     * todo: need to be moved it to reward controller
     */
    public function rewardsInCampaignEdit($id)
    {
        $title = trans('app.campaign_rewards');
        $campaign = Campaign::find($id);
        $rewards = Reward::whereCampaignId($campaign->id)->get();

        return view('admin.campaign_rewards', compact('title', 'campaign', 'rewards'));
    }

    /**
     * @param int $reward_id
     *
     * @return mixed
     */
    public function addToCart(Request $request, $reward_id = 0)
    {
        if ($reward_id) {
            // If checkout request come from reward
            session(['cart' => ['cart_type' => 'reward', 'reward_id' => $reward_id]]);

            $reward = Reward::find($reward_id);
            if ($reward->campaign->is_ended()) {
                $request->session()->forget('cart');

                return redirect()->back()->with('error', trans('app.invalid_request'));
            }
        } else {
            // Or if comes from donate button
            session([
                'cart' => [
                    'cart_type' => 'donation', 'campaign_id' => $request->campaign_id, 'amount' => $request->amount,
                ],
            ]);
        }

        return redirect(route('checkout', $request->only('auto')));
    }

    /**
     * @param        $id
     * @param null $status
     *
     * @return RedirectResponse
     */
    public function statusChange(Request $request, $id, $status = null)
    {

        $reason = $request->get('reason') ?? null;
        $helper = new AppHelper();
        $helper->campaign_status($id, $status, $reason);

        return back()->with('success', trans('app.status_updated'));
    }

    /**
     * @return mixed
     *
     * Checkout page
     */
    public function checkout()
    {
        $title = trans('app.checkout');

        if (!session('cart')) {
            return view('checkout_empty', compact('title'));
        }

        $reward = null;
        if ('reward' == session('cart.cart_type')) {
            $reward = Reward::find(session('cart.reward_id'));
            $campaign = Campaign::find($reward->campaign_id);
        } elseif ('donation' == session('cart.cart_type')) {
            $campaign = Campaign::find(session('cart.campaign_id'));
        }
        if (session('cart')) {
            return view('checkout', compact('title', 'campaign', 'reward'));
        }

        return view('checkout_empty', compact('title'));
    }

    /** @return Application|Factory|View */
    public function checkoutPost(Request $request)
    {
        $campaign = Campaign::find(session('cart.campaign_id'));

        $client = new Client();
        //        $client->setAuth('581167', 'test_ZGtY0y-_FaV-sqPvM02yOxlM4-aOvDj6jbBEZWHXI9Q');
        $client->setAuth('565297', 'live_zGwFLKuOeSEW-oGiQbTA73hp9sN75ZSSMlgSPWiLbFc');

        $amount = $request->get('amount');
        $payment = $client->createPayment(
            [
                'amount' => [
                    'value' => $amount,
                    'currency' => 'RUB',
                ],
                'confirmation' => [
                    'type' => 'redirect',
                    'return_url' => 'https://deels.ru/payment_success',
                ],
                'receipt' => [
                    'customer' => [
                        'full_name' => $request->get('full_name'),
                        'email' => $request->get('email'),
                    ],
                    'items' => [
                        [
                            'description' => 'Донат в копилку ' . $campaign->title,
                            'quantity' => '1.00',
                            'amount' => [
                                'value' => $amount,
                                'currency' => 'RUB',
                            ],
                            'vat_code' => '2',
                            'payment_mode' => 'full_prepayment',
                            'payment_subject' => 'commodity',
                        ],
                    ],
                ],
                'capture' => true,
                'description' => 'Донат в копилку ' . $campaign->title,
            ],
            uniqid('', true)
        );

        $user_id = null;
        if (Auth::check()) {
            $user_id = Auth::user()->id;
        }
        $payments_data = [
            'name' => $request->get('full_name'),
            'email' => $request->get('email'),

            'user_id' => $user_id,
            'campaign_id' => $campaign->id,
            'reward_id' => session('cart.reward_id'),

            'amount' => $amount,
            'payment_method' => 'kassa',
            'status' => 'initial',
            'currency' => 'RUB',
            'local_transaction_id' => $payment->getId(),

            'contributor_name_display' => session('cart.contributor_name_display'),
        ];
        // Create payment and clear it from session
        Payment::create($payments_data);
        $request->session()->forget('cart');

        return redirect($payment->getConfirmation()->getConfirmationUrl());
    }

    public function confirmPayment(Request $request)
    {
        $source = file_get_contents('php://input');
        file_put_contents(base_path() . '/kassa.json', $source);

        $requestBody = json_decode($source, true);

        if ('CONFIRMED' === $requestBody['Status']) {
            $orderData = explode('_', $requestBody['OrderId']);

            $userId = 'anon' === $orderData[1] ? null : $orderData[1];
            $email = $request->get('email', $orderData[3] ?? null);
            Log::info('Payment order info', $orderData);
            $payments_data = [
                'name' => $request->get('full_name'),
                'email' => $email,

                'user_id' => $userId,
                'campaign_id' => $orderData[2],
                'reward_id' => null,

                'amount' => $requestBody['Amount'] / 100,
                'payment_method' => 'tinkoff',
                'status' => 'pending',
                'currency' => 'RUB',

                'contributor_name_display' => session('cart.contributor_name_display'),
            ];
            if ('anon' !== $orderData[1] && isset($requestBody['RebillId'])) {
                $payments_data['rebill_id'] = $requestBody['RebillId'];
            }
            // Create payment and clear it from session
            $payment = Payment::updateOrCreate(
                ['local_transaction_id' => $requestBody['PaymentId']],
                $payments_data
            );
            $request->session()->forget('cart');

            $url = route('deels.public.campaigns.show', ['slug' => $payment->campaign->slug]);

            try {
                Mail::send(
                    [],
                    [],
                    function (Message $message) use ($payment, $url): void {
                        $message
                            ->from(env('MAIL_FROM_ADDRESS', 'info@deels.ru'), 'DEELS')
                            ->to($payment->campaign->user->email)
                            ->subject('Новый взнос в копилку на DEELS')
                            ->html('<a href="' . $url . '"><img src="https://kopiberi.ru/email_banners/Frame 36073.jpg" style="max-width: 100%; height:auto;"></a>');
                    }
                );
            } catch (\Throwable $e) {

            }


            $thankText = 'Вам пополнили копилку! Для того, чтобы сумма зачислилась на баланс, оставьте в благодарность';
            FireBaseEvent::dispatch( $payment->campaign->user_id, 'Вашу копилку только что пополнили!', $payment->campaign->id, 'campaign');
            if (($requestBody['Amount'] / 100) < 100) {
                $thankText .= ' текстовую благодраность';
            } elseif (($requestBody['Amount'] / 100) < 300) {
                $thankText .= ' фото';
            } elseif (($requestBody['Amount'] / 100) < 300) {
                $thankText .= ' голосовое сообщение';
            } elseif (($requestBody['Amount'] / 100) < 300) {
                $thankText .= ' видео';
            }


            $thankText .= '<br>Перейти к копилке:<br><a href="' . $url . '">' . $url . '</a>';

            if ($userId && $userId == $payment->campaign->user->id) {
                $payment->update(['status' => 'success']);
            } else {
                Mail::send(
                    [],
                    [],
                    function (Message $message) use ($payment, $thankText): void {
                        $message
                            ->from(env('MAIL_FROM_ADDRESS', 'info@kopiberi.ru'))
                            ->to($payment->campaign->user->email)
                            ->subject('Благодарность за донат на DEELS')
                            ->html($thankText);
                    }
                );
            }


            if ($email) {
                Mail::send(
                    [],
                    [],
                    function (Message $message) use ($payment): void {
                        $message
                            ->from(env('MAIL_FROM_ADDRESS', 'info@kopiberi.ru'))
                            ->to($payment->campaign->user->email)
                            ->subject('Донат на DEELS')
                            ->html('<img src="https://kopiberi.ru/email_banners/for_donater.png" style="max-width: 100%; height:auto;">');
                    }
                );
            }

            if ($userId) {
                Log::info("FOUND USER ID " . $userId);
                $invitedUser = User::find($userId);
                if ($invitedUser && $inviteToken = $invitedUser->invite_referral_code) {
                    Log::info("FOUND INVITED USER " . $invitedUser->invite_referral_code);
                    $invitor = User::where('referral_code', $inviteToken)->first();
                    $myDonaters = User::query()
                        ->where('invite_referral_code', $invitor->referral_code)
                        ->pluck('id');
                    $percent = 0;
                    if ($myDonaters->count() >= 5) {
                        $percent = 0.25;
                    }
                    if ($myDonaters->count() >= 10) {
                        $percent = 0.5;
                    }

                    if ($invitor) {
                        $payments_data = [
                            'name' => $invitedUser->name,
                            'email' => $invitedUser->email,

                            'user_id' => $invitedUser->id,
                            'campaign_id' => null,
                            'reward_id' => null,

                            'amount' => ($requestBody['Amount'] / 100) * ($percent > 0 ? $percent : 0.25) / 100,
                            'payment_method' => 'referral',
                            'status' => 'pending',
                            'currency' => 'RUB',

                            'contributor_name_display' => session('cart.contributor_name_display'),
                        ];
                        Log::info("FOUND INVITOR USER " . $invitor->id);

                        if ($percent > 0) {
                            Log::info("FOUND PERCENT " . $percent);
                            Campaign::query()
                                ->where('user_id', $invitor->id)
                                ->orderBy('id')
                                ->get()
                                ->each(function (Campaign $campaign) use (&$payments_data): void {
                                    if ($campaign->percent_raised() < 100) {
                                        $payments_data['campaign_id'] = $campaign->id;
                                        Payment::create(
                                            $payments_data
                                        );
                                        Log::info("PAYED REFERRAL " . $campaign->id);
                                    }
                                });
                        }

                        PendingReferrals::create([
                            'user_id' => $invitor->id,
                            'paid' => (bool)$payments_data['campaign_id'],
                            'data' => $payments_data,
                        ]);
                        Log::info("SAVED REFERRAL ");
                    }
                }
            }
        }

        return 'OK';
    }

    public function successPayment()
    {
        $title = trans('app.payment_success');

        return view('payment_success', compact('title'));
    }

    /**
     * @return mixed
     *
     * Payment gateway PayPal
     */
    public function paypalRedirect(Request $request)
    {
        if (!session('cart')) {
            return view('checkout_empty', compact('title'));
        }
        // Find the campaign
        $cart = session('cart');

        $amount = 0;
        if ('reward' == session('cart.cart_type')) {
            $reward = Reward::find(session('cart.reward_id'));
            $amount = $reward->amount;
            $campaign = Campaign::find($reward->campaign_id);
        } elseif ('donation' == session('cart.cart_type')) {
            $campaign = Campaign::find(session('cart.campaign_id'));
            $amount = $cart['amount'];
        }
        $currency = get_option('currency_sign');
        $user_id = null;
        if (Auth::check()) {
            $user_id = Auth::user()->id;
        }
        // Create payment in database

        $transaction_id = 'tran_' . time() . str_random(6);
        // get unique recharge transaction id
        while (Payment::whereLocalTransactionId($transaction_id)->count() > 0) {
            $transaction_id = 'reid' . time() . str_random(5);
        }
        $transaction_id = strtoupper($transaction_id);

        $payments_data = [
            'name' => session('cart.full_name'),
            'email' => session('cart.email'),

            'user_id' => $user_id,
            'campaign_id' => $campaign->id,
            'reward_id' => session('cart.reward_id'),

            'amount' => $amount,
            'payment_method' => 'paypal',
            'status' => 'initial',
            'currency' => $currency,
            'local_transaction_id' => $transaction_id,

            'contributor_name_display' => session('cart.contributor_name_display'),
        ];
        // Create payment and clear it from session
        $created_payment = Payment::create($payments_data);
        $request->session()->forget('cart');

        // PayPal settings
        $paypal_action_url = 'https://www.paypal.com/cgi-bin/webscr';
        if (1 == get_option('enable_paypal_sandbox')) {
            $paypal_action_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
        }

        $paypal_email = get_option('paypal_receiver_email');
        $return_url = route('payment_success', $transaction_id);
        $cancel_url = route('checkout');
        $notify_url = route('paypal_notify', $transaction_id);

        $item_name = $campaign->title . ' [Contributing]';

        // Check if paypal request or response
        $querystring = '';

        // Firstly Append paypal account to querystring
        $querystring .= '?business=' . urlencode($paypal_email) . '&';

        // Append amount& currency (£) to quersytring so it cannot be edited in html
        // The item name and amount can be brought in dynamically by querying the $_POST['item_number'] variable.
        $querystring .= 'item_name=' . urlencode($item_name) . '&';
        $querystring .= 'amount=' . urlencode($amount) . '&';
        $querystring .= 'currency_code=' . urlencode($currency) . '&';

        $querystring .= 'first_name=' . urlencode(session('cart.full_name')) . '&';
        // $querystring .= "last_name=".urlencode($ad->user->last_name)."&";
        $querystring .= 'payer_email=' . urlencode(session('cart.email')) . '&';
        $querystring .= 'item_number=' . urlencode($created_payment->local_transaction_id) . '&';

        // loop for posted values and append to querystring
        foreach (array_except($request->input(), '_token') as $key => $value) {
            $value = urlencode(stripslashes($value));
            $querystring .= "$key=$value&";
        }

        // Append paypal return addresses
        $querystring .= 'return=' . urlencode(stripslashes($return_url)) . '&';
        $querystring .= 'cancel_return=' . urlencode(stripslashes($cancel_url)) . '&';
        $querystring .= 'notify_url=' . urlencode($notify_url);

        // Append querystring with custom field
        // $querystring .= "&custom=".USERID;

        // Redirect to paypal IPN
        header('location:' . $paypal_action_url . $querystring);
        exit;
    }

    /**
     * @param $transaction_id
     *
     * Check paypal notify
     */
    public function paypalNotify(Request $request, $transaction_id): void
    {
        // todo: need to  be check
        $payment = Payment::whereLocalTransactionId($transaction_id)->where('status', '!=', 'success')->first();

        $verified = paypal_ipn_verify();
        if ($verified) {
            // Payment success, we are ready approve your payment
            $payment->status = 'success';
            $payment->charge_id_or_token = $request->txn_id;
            $payment->description = $request->item_name;
            $payment->payer_email = $request->payer_email;
            $payment->payment_created = strtotime($request->payment_date);
            $payment->save();
        } else {
            $payment->status = 'declined';
            $payment->description = trans('app.payment_declined_msg');
            $payment->save();
        }
        // Reply with an empty 200 response to indicate to paypal the IPN was received correctly
        header('HTTP/1.1 200 OK');
    }

    /**
     * @return array
     *
     * receive card payment from stripe
     */
    public function paymentStripeReceive(Request $request)
    {
        $user_id = null;
        if (Auth::check()) {
            $user_id = Auth::user()->id;
        }

        $stripeToken = $request->stripeToken;
        Stripe::setApiKey(get_stripe_key('secret'));
        // Create the charge on Stripe's servers - this will charge the user's card
        try {
            $cart = session('cart');

            // Find the campaign
            $amount = 0;
            if ('reward' == session('cart.cart_type')) {
                $reward = Reward::find(session('cart.reward_id'));
                $amount = $reward->amount;
                $campaign = Campaign::find($reward->campaign_id);
            } elseif ('donation' == session('cart.cart_type')) {
                $campaign = Campaign::find(session('cart.campaign_id'));
                $amount = $cart['amount'];
            }
            $currency = get_option('currency_sign');

            // Charge from card
            $charge = Charge::create([
                'amount' => get_stripe_amount($amount), // amount in cents, again
                'currency' => $currency,
                'source' => $stripeToken,
                'description' => $campaign->title . ' [Contributing]',
            ]);

            if ('succeeded' == $charge->status) {
                // Save payment into database
                $data = [
                    'name' => session('cart.full_name'),
                    'email' => session('cart.email'),
                    'amount' => get_stripe_amount($charge->amount, 'to_dollar'),

                    'user_id' => $user_id,
                    'campaign_id' => $campaign->id,
                    'reward_id' => session('cart.reward_id'),
                    'payment_method' => 'stripe',
                    'currency' => $currency,
                    'charge_id_or_token' => $charge->id,
                    'description' => $charge->description,
                    'payment_created' => $charge->created,

                    // Card Info
                    'card_last4' => $charge->source->last4,
                    'card_id' => $charge->source->id,
                    'card_brand' => $charge->source->brand,
                    'card_country' => $charge->source->US,
                    'card_exp_month' => $charge->source->exp_month,
                    'card_exp_year' => $charge->source->exp_year,

                    'contributor_name_display' => session('cart.contributor_name_display'),
                    'status' => 'success',
                ];

                Payment::create($data);

                $request->session()->forget('cart');

                return ['success' => 1, 'msg' => trans('app.payment_received_msg'), 'response' => $this->payment_success_html()];
            }
        } catch (Card $e) {
            // The card has been declined
            return ['success' => 0, 'msg' => trans('app.payment_declined_msg'), 'response' => $e];
        }
    }

    /** @return string */
    public function payment_success_html()
    {
        $html = ' <div class="payment-received">
                            <h1> <i class="fa fa-check-circle-o"></i> ' . trans('app.payment_thank_you') . '</h1>
                            <p>' . trans('app.payment_receive_successfully') . '</p>
                            <a href="' . route('home') . '" class="btn btn-filled">' . trans('app.home') . '</a>
                        </div>';

        return $html;
    }

    /**
     * @param null $transaction_id
     *
     * @return Application|Factory|View
     */
    public function paymentSuccess(Request $request, $transaction_id = null)
    {
        if ($transaction_id) {
            $payment = Payment::whereLocalTransactionId($transaction_id)->whereStatus('initial')->first();
            if ($payment) {
                $payment->status = 'pending';
                $payment->save();
            }
        }

        $title = trans('app.payment_success');

        return view('payment_success', compact('title'));
    }

    /**
     * @date  April 29, 2017
     *
     * @since v.1.1
     */
    public function paymentBankTransferReceive(Request $request)
    {
        $rules = [
            'bank_swift_code' => 'required',
            'account_number' => 'required',
            'branch_name' => 'required',
            'branch_address' => 'required',
            'account_name' => 'required',
        ];
        $this->validate($request, $rules);

        // get Cart Item
        if (!session('cart')) {
            return view('checkout_empty', compact('title'));
        }
        // Find the campaign
        $cart = session('cart');

        $amount = 0;
        if ('reward' == session('cart.cart_type')) {
            $reward = Reward::find(session('cart.reward_id'));
            $amount = $reward->amount;
            $campaign = Campaign::find($reward->campaign_id);
        } elseif ('donation' == session('cart.cart_type')) {
            $campaign = Campaign::find(session('cart.campaign_id'));
            $amount = $cart['amount'];
        }
        $currency = get_option('currency_sign');
        $user_id = null;
        if (Auth::check()) {
            $user_id = Auth::user()->id;
        }
        // Create payment in database

        $transaction_id = 'tran_' . time() . str_random(6);
        // get unique recharge transaction id
        while (Payment::whereLocalTransactionId($transaction_id)->count() > 0) {
            $transaction_id = 'reid' . time() . str_random(5);
        }
        $transaction_id = strtoupper($transaction_id);

        $payments_data = [
            'name' => session('cart.full_name'),
            'email' => session('cart.email'),

            'user_id' => $user_id,
            'campaign_id' => $campaign->id,
            'reward_id' => session('cart.reward_id'),

            'amount' => $amount,
            'payment_method' => 'bank_transfer',
            'status' => 'pending',
            'currency' => $currency,
            'local_transaction_id' => $transaction_id,

            'contributor_name_display' => session('cart.contributor_name_display'),

            'bank_swift_code' => $request->bank_swift_code,
            'account_number' => $request->account_number,
            'branch_name' => $request->branch_name,
            'branch_address' => $request->branch_address,
            'account_name' => $request->account_name,
            'iban' => $request->iban,
        ];
        // Create payment and clear it from session
        $created_payment = Payment::create($payments_data);
        $request->session()->forget('cart');

        return ['success' => 1, 'msg' => trans('app.payment_received_msg'), 'response' => $this->payment_success_html()];
    }

    /**
     * @param $reward_id
     *
     * @return BinaryFileResponse
     */
    public function rewardDigitalDownloads($reward_id)
    {
        $reward = Reward::find($reward_id);
        if (!$reward) {
            abort(404);
        }
        $user = Auth::user();

        $verify_payment = Payment::whereUserId($user->id)->whereRewardId($reward_id)->whereStatus('success')->first();
        if (!$verify_payment) {
            abort(404);
        }

        $media_download = get_media($reward->digital_downloads);

        $pathToFile = './uploads/' . $media_download->slug_ext;
        $name = $media_download->name;
        $headers = ['Content-Type: ' . $media_download->mime_type];

        return response()->download($pathToFile, $name, $headers);
    }

    public function sliderOrder(Campaign $campaign, Request $request): void
    {
        $campaign->update(['slider_order' => $request->get('order', 0)]);
    }

    public function campaign_donate(Request $request)
    {
        $user = Auth::user() ?? auth()->user();
        if ($request->input('user_id')) {
            $user = User::find($request->input('user_id'));
        }
        $campaign_id = $request->input('campaign_id');
        $campaign = Campaign::find($campaign_id);
        $donation_amount = $request->input('donation_amount') ?? $request->input('amount');
        if ($donation_amount <= 0) {
            return response()->json([
                'success' => false,
                'error' => 'Некорректное значение',
            ]);
        }
        try {
            $user->wallet_withdraw(intval($donation_amount), ['donate' => 'campaign', 'description' => 'Оплата копилки '.$campaign->title]);
            app(ReferralBonusService::class)->awardForFirstDonate($user);
            FireBaseEvent::dispatch( $campaign->user_id, 'Вашу копилку только что пополнили!', $campaign_id, 'campaign');
            if($request->input('auto')) {
                FireBaseEvent::dispatch( $campaign->user_id, 'Пользователь подписался на ежемесячное пополнение вашей копилки!', $campaign->id, 'campaign');
            }

            $payments = Payment::where('user_id', Auth::user()->id)
                ->where('campaign_id', $campaign_id)
                ->whereNotNull('rebill_id')
                ->update(['rebill_id' => null]);
            $status = 'pending';
            $auto_thanks = false;
            if($user->id == $campaign->user_id) {
                $status = 'success';
                $auto_thanks = true;
            }

            $payment = Payment::create([
                'name' => Auth::user()->name,
                'email' => Auth::user()->email,
                'campaign_id' => $campaign_id,
                'user_id' => Auth::id(),
                'amount' => ($donation_amount / 100),
                'payment_method' => 'coins',
                'status' => $status,
                'rebill_id' => $request->input('auto') ? 1 : null,
                'rebill_at' => $request->input('auto') ? Carbon::now()->addMonth() : null,
            ]);

            if($auto_thanks) {
                $data = [
                    'payment_id' => $payment->id,
                    'data' => ['type' => 'comment', 'payload' => '']
                ];
                Thanks::create($data);
            }
            $helper = new AppHelper();
            $text = 'Пользователь ' . $user->name . ' внес вклад в вашу копилку ' . $campaign->title;
            $button = [
                'type' => 'campaign',
                'campaign_id' => $campaign->id,
                'text' => 'Перейти и поблагодарить',
                'url' => route('deels.public.campaigns.show', ['slug' => $campaign->slug]) . '?thanks=' . $payment->id
            ];
            $helper->chat_notify($campaign->user, $text, $button);
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'user_id' => $user->id,
                    'message' => 'Вклад выполнен успешно!'
                ]);
            } else {
                return redirect(route('deels.public.campaigns.show', ['slug' => $campaign->slug]))
                    ->with('success', 'Вклад выполнен успешно!');
            }
        } catch (\Throwable $e) {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'balance' => intval($user->balance),
                    'error' => $e->getMessage(),
                ]);
            } else {
                return back()->with('error', $e->getMessage());
            }
        }
    }
}
