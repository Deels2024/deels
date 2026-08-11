<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Services\MediaUploadService;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    public function __construct(
        private MediaUploadService $mediaUploadService
    ) {
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $videos = Media::latest()->paginate(5);

        return view('videos.index', compact('videos'))
            ->with('i', (request()->input('page', 1) - 1) * 5);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('videos.form');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $media = $this->mediaUploadService->storeApiVideo($request);

        if (!$media) {
            return null;
        }

        return response()->json([
            'success' => 1,
            'message' => 'Video uploaded successfully.',
        ]);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Media  $media
     * @return \Illuminate\Http\Response
     */
    public function destroy($media)
    {
        $media = Media::find($media);

        if (isset($media->file_name) && !empty($media->file_name)) {
            $path = 'videos/';
            $store_path = $path . $media->file_name;
            \Storage::disk('public')->delete($store_path);
        }
        $media->delete();

        return redirect()->route('videos.index')
            ->with('success', 'video deleted successfully');
    }

    public function show() {

    }
    public function edit() {

    }
    public function update() {

    }


}
