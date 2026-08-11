<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;

/**
 * Class MediaController.
 */
class TagController extends Controller
{
    /** @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View */
    public function index(Request $request)
    {
        $tags = Tag::with('stories')->withCount('stories')->orderBy('stories_count', 'desc')->paginate(20);
        $title = 'Теги';

        return view('admin.tags', compact('title', 'tags'));
    }
}
