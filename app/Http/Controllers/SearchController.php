<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\CampaignStoreRequest;
use App\Models\Campaign;
use App\Models\Category;
use App\Models\Country;
use App\Models\Likes;
use App\Models\Payment;
use App\Models\PendingReferrals;
use App\Models\Reward;
use App\Models\Story;
use App\Models\Tag;
use App\Models\Thanks;
use App\Models\User;
use App\Services\CampaignFilterService;
use App\Services\Contests\ContestVisibilityService;
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
use Illuminate\Support\Facades\DB;
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
class SearchController extends Controller
{
    /**
     * @return Factory|View
     *
     * Search Campaigns
     */
    public function search(Request $request)
    {
        if ($request->q) {
            $q = $request->q;
            $suggest = $request->suggest;
            $title = 'Поиск';
            $paginate = 20;
            if (request()->wantsJson()) {
                $paginate = 20;
            }

            $stories = [];
            $campaigns_query = Campaign::active();
            $stories_query = Story::query();
            if($suggest) {
                $campaigns_query = Campaign::active()
                    ->select('id', 'title', DB::raw("'campaign' as type"))
                    ->whereRaw("lower(title) like '%{$q}%'");

                $tags = Tag::select('id', 'title', DB::raw("'tag' as type"))
                    ->where('title', 'like', '%'.$request->q.'%');
                $campaigns_query = $campaigns_query->union($tags);
            } else {
//                $campaigns_query->whereRaw("lower(title) like '%{$request->q}%'")
//                    ->orWhereRaw("lower(short_description) like '%{$request->q}%'")
//                    ->orWhereRaw("lower(description) like '%{$request->q}%'")
//                    ->active();

                // Экранируем спецсимволы в запросе
                $searchTerm = preg_quote($request->q, '/');

                // Регулярное выражение для поиска полного слова (перед и после должно быть словоразделяющее)
                $regex = '\\b' . $searchTerm . '\\b';  // \\b — это граница слова

                $campaigns_query->whereRaw("lower(title) REGEXP ?", [$regex])
                    ->orWhereRaw("lower(short_description) REGEXP ?", [$regex])
                    ->orWhereRaw("lower(description) REGEXP ?", [$regex])
                    ->active();


                $stories_query = Story::whereHas('tags', function($query) use($q) {
                    $query->where('title', $q);
                })->where('active', true)->where('declined', false);
                app(ContestVisibilityService::class)->applyToStories(
                    $stories_query,
                    Auth::user() ?? auth()->user()
                );

                $stories = $stories_query->paginate($paginate);

                if (request()->wantsJson()) {
                    $data = [];

                    foreach ($stories as $media_item) {
                        $is_liked = false;
                        $is_viewed = false;
                        $user_data = (new ApiController())->account_info($media_item->user_id, true, true);

                        $media_item->campaign = $media_item->campaign();
                        $media_item->user = $user_data;
                        $media_item->is_liked = $is_liked;
                        $media_item->is_viewed = $is_viewed;
                        if (in_array($media_item->id, [211, 212, 213, 214])) {
                            $media_item->comments_count = 10;
                            $media_item->likes_count = 5000;
                        }

                        unset($media_item->comments);
                        unset($media_item->likes);


                        $data[] = $media_item;
                    }

                    $stories = $data;
                }


            }




            $campaigns = $campaigns_query->paginate($paginate);



            $search_time = number_format(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'], 2);
            if($suggest) {
                $campaigns = $campaigns_query->distinct()->paginate($paginate)->pluck('title');
                $campaigns = $campaigns->unique()
                    ->sortBy(fn($item) => strlen($item))
                    ->values();
                if (request()->wantsJson()) {
                    return response()->json([
                        'suggest' => $campaigns,
                        'q' => $q,
                        'search_time' => $search_time,

                    ]);
                }
            }

            // Применяем scopeWithCampaignData к коллекции из пагинации
            $campaigns->getCollection()->transform(function ($campaign) {
                return Campaign::formatCampaignData($campaign);
            });

            $users = User::where('username', 'like', '%'.$q.'%')->orWhere('name', 'like', '%'.$q.'%')->paginate(20);
            if (request()->wantsJson()) {
                return response()->json([
                    'campaigns' => $campaigns,
                    'stories' => $stories,
                    'users' => $users,
                    'current_page_campaigns' => $campaigns->currentPage(),
                    'total_pages_campaigns' => $campaigns->lastPage(),
                    'current_page_users' => $users->currentPage(),
                    'total_pages_users' => $users->lastPage(),
                    'q' => $q,
                    'search_time' => $search_time,

                ]);
            }

            return view('search', compact('title', 'campaigns', 'q', 'search_time','users', 'stories'));
        }
    }
}
