<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Battle;
use App\Models\Campaign;
use App\Models\Challenge;
use App\Models\Story;
use App\Services\Contests\ContestListService;
use App\Services\Contests\ContestVisibilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class DeelsPublicController extends Controller
{
    public function battles(Request $request)
    {
        $request->merge(['content' => 'battles']);
        $media = app(ContestListService::class)->contests($request);

        return view('challenges_index', ['challenges' => $media]);
    }

    public function story(Request $request, int $id)
    {
        $query = Story::with('user')
            ->where('active', true)
            ->where('declined', false)
            ->where('banned', false);

        app(ContestVisibilityService::class)->applyToStories(
            $query,
            Auth::user() ?? auth()->user()
        );

        $story = $query->findOrFail($id);
        $preview = $story->getStoryPreview();
        $title = $story->title ?: 'История участника Deels';
        $descriptionSource = (string) ($story->description ?? $title);
        $seoDescription = Str::limit(trim(strip_tags($descriptionSource)), 160);
        $seoImage = $preview['type'] === 'video'
            ? ($preview['poster'] ?? null)
            : ($preview['url'] ?? null);

        return view('stories.page_public', compact(
            'story',
            'preview',
            'title',
            'seoDescription',
            'seoImage'
        ));
    }

    public function sitemap()
    {
        $urls = Cache::remember('deels.public.sitemap.v1', now()->addMinutes(15), function (): array {
            $items = [
                ['loc' => route('home'), 'lastmod' => null, 'priority' => '1.0', 'changefreq' => 'daily'],
                ['loc' => route('challenges.catalog'), 'lastmod' => null, 'priority' => '0.9', 'changefreq' => 'daily'],
                ['loc' => route('deels.public.battles.index'), 'lastmod' => null, 'priority' => '0.9', 'changefreq' => 'daily'],
                ['loc' => route('stories.catalog'), 'lastmod' => null, 'priority' => '0.8', 'changefreq' => 'daily'],
                ['loc' => route('deels.public.campaigns.index'), 'lastmod' => null, 'priority' => '0.8', 'changefreq' => 'daily'],
            ];

            $publicContest = static function ($query) {
                return $query
                    ->where('active', true)
                    ->where('declined', false)
                    ->where(function ($visibility): void {
                        $visibility->whereNull('visibility')->orWhere('visibility', 'all');
                    });
            };

            $challenges = $publicContest(Challenge::query())
                ->select(['id', 'updated_at'])
                ->latest('updated_at')
                ->limit(5000)
                ->get();
            foreach ($challenges as $challenge) {
                $items[] = [
                    'loc' => route('deels.public.challenges.show', $challenge->id),
                    'lastmod' => optional($challenge->updated_at)->toAtomString(),
                    'priority' => '0.8',
                    'changefreq' => 'daily',
                ];
            }

            $battles = $publicContest(Battle::query())
                ->select(['id', 'updated_at'])
                ->latest('updated_at')
                ->limit(5000)
                ->get();
            foreach ($battles as $battle) {
                $items[] = [
                    'loc' => route('deels.public.battles.show', $battle->id),
                    'lastmod' => optional($battle->updated_at)->toAtomString(),
                    'priority' => '0.8',
                    'changefreq' => 'daily',
                ];
            }

            $storyQuery = Story::query()
                ->where('active', true)
                ->where('declined', false)
                ->where('banned', false);
            app(ContestVisibilityService::class)->applyToStories($storyQuery, null);
            $stories = $storyQuery
                ->select(['id', 'updated_at'])
                ->latest('updated_at')
                ->limit(5000)
                ->get();
            foreach ($stories as $story) {
                $items[] = [
                    'loc' => route('deels.public.stories.show', $story->id),
                    'lastmod' => optional($story->updated_at)->toAtomString(),
                    'priority' => '0.7',
                    'changefreq' => 'weekly',
                ];
            }

            $campaigns = Campaign::query()
                ->active()
                ->whereNotNull('slug')
                ->select(['slug', 'updated_at'])
                ->latest('updated_at')
                ->limit(5000)
                ->get();
            foreach ($campaigns as $campaign) {
                $items[] = [
                    'loc' => route('deels.public.campaigns.show', $campaign->slug),
                    'lastmod' => optional($campaign->updated_at)->toAtomString(),
                    'priority' => '0.7',
                    'changefreq' => 'weekly',
                ];
            }

            return $items;
        });

        return response()
            ->view('sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
