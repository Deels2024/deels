<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Media;
use App\Services\MediaUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Class MediaController.
 */
class MediaController extends Controller
{
    /** @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View */
    public function loadFileManager(Request $request)
    {
        if (!$request->ajax()) {
            abort(404);
        }
        $user        = Auth::user();
        $media_query = $user->medias();

        if (!empty($request->filter_name)) {
            $media_query = $media_query->where('name', 'like', "%{$request->filter_name}%");
        }

        $media_query    = $media_query->orderBy('id', 'desc')->paginate(30);
        $data['medias'] = $media_query;

        return view('inc.filemanager_content', $data);
    }

    /** @return array */
    public function store(Request $request, $path = 'challenges')
    {
        return app(MediaUploadService::class)->store($request, $path);
    }

    /**
     * @param  null  $media_ids
     *
     * @return array
     */
    public function delete(Request $request, $media_ids = null)
    {
        if (config('app.is_demo')) {
            return ['success' => false, 'msg' => __('app.feature_disable_demo')];
        }

        if (!$media_ids) {
            $media_ids = $request->media_ids;
        }
        if (!empty($media_ids)) {
            if (!\is_array($media_ids)) {
                $media_ids = explode(',', $media_ids);
            }

            if (\is_array($media_ids)) {
                try {
                    foreach ($media_ids as $media_id) {
                        $media = Media::find($media_id);
                        if ($media) {
                            $media_name = $media->slug_ext;

                            // Deleting from database
                            $media->delete();

                            // Deleting from storage
                            $storage = current_disk();
                            if ('image' == substr($media->mime_type, 0, 5)) {
                                $image_sizes = config('media.size');

                                // Get all image size and delete form them
                                foreach ($image_sizes as $ikey => $ivalue) {
                                    $media_path = "uploads/images/{$ikey}/{$media_name}";

                                    if ($storage->has($media_path)) {
                                        $storage->delete($media_path);
                                    }
                                }

                                // Delete original file
                                $media_path = "uploads/images/{$media_name}";
                                if ($storage->has($media_path)) {
                                    $storage->delete($media_path);
                                }
                            }

                            // deleting any other file
                            $media_path = "uploads/{$media_name}";
                            if ($storage->has($media_path)) {
                                $storage->delete($media_path);
                            }
                        }
                    }
                } catch (\Exception $e) {
                    return ['success' => false, 'msg' => $e->getMessage()];
                }
            }
        }

        return ['success' => true, 'msg' => __('app.media_deleted')];
    }
}
