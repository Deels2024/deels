<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Story;
use App\Services\Contests\ContestListService;
use App\Services\Contests\ContestVisibilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
}
