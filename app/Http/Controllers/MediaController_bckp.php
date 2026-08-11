<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Media;
use FFMpeg\FFMpeg;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Facades\Image;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Class MediaController.
 */
class MediaController_bckp extends Controller
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
    public function store(Request $request)
    {
        $user_id            = Auth::user()->id ?? $request->input('user_id');
        $allowed_file_types = config('app.allowed_files');
        $story = $request->input('story');
        $challenge = $request->input('challenge');
        $upload_dir  = 'uploads/images/';
        $upload_path = 'uploads/images/';
        if($story) {
            $upload_dir = '/uploads/stories/thumbs/';
            $upload_path = '/uploads/stories';
        }
        if($challenge) {
            $upload_dir = '/uploads/challenges/thumbs/';
            $upload_path = '/uploads/challenges';
        }

        if($request->file('video')) {
            $getFilename = md5(microtime());
            $filename = $getFilename.'.mp4';
            $slug_ext = '.mp4';
            $target_file = public_path($upload_path . '/'.$filename);
            $converted_filename_ext = 'c_' . $filename;
            $videoUrl_converted = public_path('uploads/stories/' . $converted_filename_ext);
            move_uploaded_file(
                $_FILES['video']['tmp_name'],
                $target_file
            );

//            $uploaded_data   = [
//                'user_id'   => $user_id,
//                'name'      => 'c_' . $getFilename,
//                'slug'      => 'c_' . $getFilename,
//                'slug_ext'  => $converted_filename_ext,
//                'file_size' => '0',
//                'mime_type' => 'video/mp4',
//            ];
//            $is_uploaded     = Media::create($uploaded_data);

            $ffmpeg = FFMpeg::create([
                'ffmpeg.binaries' => env('FFMPEG'),
                'ffprobe.binaries' => env('FFPROBE')
            ]);
            $video = $ffmpeg->open($target_file);
            $format = new \FFMpeg\Format\Video\X264('aac', 'libx264');
            $format->setKiloBitrate(20000);
            $format->setAdditionalParameters([
                '-preset', 'slow',
                '-crf', '18',
                '-pix_fmt', 'yuv420p',
            ]);
//            $video->save(new \FFMpeg\Format\Video\X264(), $videoUrl_converted);
            $video->save($format, $videoUrl_converted);
            File::delete($target_file);
            $uploaded_data   = [
                'user_id'   => $user_id,
                'name'      => 'c_' . $getFilename,
                'slug'      => 'c_' . $getFilename,
                'slug_ext'  => $converted_filename_ext,
                'file_size' => '0',
                'mime_type' => 'video/mp4',
            ];
            $is_uploaded     = Media::create($uploaded_data);
            $uploadedFiles[] = $is_uploaded;
            return ['success' => true, 'msg' => __('app.media_uploaded'), 'images' => $uploadedFiles];
        }

        if ($request->files->has('files')) {
            $files = $request->files->get('files');
            $uploadedFiles = [];

            try {
                foreach ($files as $file) {
                    $getFilename = $file->getClientOriginalName();
                    $clientExt   = $file->getClientOriginalExtension();
                    $getFilename = md5(microtime()).'.'.$clientExt;
//                    $md5Name = md5_file($file->getRealPath());
//                    $guessExtension = $file->guessExtension();
//                    dd($file->getClientOriginalName(),$file->getRealPath(),$md5Name,$guessExtension);

                    $ext = '.' . $clientExt;

                    $baseSlug = str_replace($ext, '', $getFilename);

                    $getMimeType = $file->getClientMimeType();
                    $getSize     = $file->getSize();

                    $slug     = strtolower($baseSlug);
                    $slug     = unique_slug($slug, 'Media');
                    $slug_ext = $slug . $ext;

                    if ('image' == substr($getMimeType, 0, 5)) {
                        // It's imgae file
                        $image       = $file;
                        $image_sizes = config('media.size');


                        foreach ($image_sizes as $ikey => $ivalue) {
                            $img_thumb_name = $upload_dir . $ikey . '/' . $slug_ext;

                            $resized = Image::make($image)
                                            ->orientate()
                                            ->resize($ivalue[0], $ivalue[1], function($constraint): void {
                                                $constraint->aspectRatio();
                                            })
                                            ->encode('webp', 100)
                                            ->stream();
                            // upload thumb image
                            current_disk()->put($img_thumb_name, $resized->__toString(), 'public');
                        }
                        current_disk()->putFileAs($upload_path, $file, $slug_ext, 'public');
                        $uploaded_data   = [
                            'user_id'   => $user_id,
                            'name'      => $getFilename,
                            'slug'      => $slug,
                            'slug_ext'  => $slug_ext,
                            'file_size' => $getSize,
                            'mime_type' => $getMimeType,
                        ];
                    } else {

                        $upload_path = 'uploads/';
                        if($story) {
                            $upload_dir = '/uploads/stories/thumbs/';
                            $upload_path = '/uploads/stories';
                            if (!File::isDirectory($upload_path)) {
                                File::makeDirectory($upload_path, 0777, true, true);
                            }
                        }
                        if($challenge) {
                            $upload_dir = '/uploads/challenges/thumbs/';
                            $upload_path = '/uploads/challenges';
                            if (!File::isDirectory($upload_path)) {
                                File::makeDirectory($upload_path, 0777, true, true);
                            }
                        }

                        $data = current_disk()->putFileAs($upload_path, $file, $slug_ext, 'public');

                        $ffmpeg = FFMpeg::create([
                            'ffmpeg.binaries' => env('FFMPEG'),
                            'ffprobe.binaries' => env('FFPROBE')
                        ]);


                        try {
                            // Open the video file
                            $video = $ffmpeg->open(public_path($upload_path.'/'.$slug_ext));
                            $converted_filename_ext = 'c_' . $baseSlug.'.mp4';

                            $videoUrl_converted = public_path($upload_path .'/'. $converted_filename_ext);
                            $format = new \FFMpeg\Format\Video\X264('aac', 'libx264');
                            $format->setKiloBitrate(20000);
                            $format->setAdditionalParameters([
                                '-preset', 'slow',
                                '-crf', '18',
                                '-pix_fmt', 'yuv420p',
                            ]);
//                            $video->save(new \FFMpeg\Format\Video\X264(), $videoUrl_converted);
                            $video->save($format, $videoUrl_converted);
                            // Ensure the file exists before getting its size
                            $fileSize = file_exists($videoUrl_converted) ? filesize($videoUrl_converted) : 0;
                            $uploaded_data   = [
                                'user_id'   => $user_id,
                                'name'      => 'c_' . $getFilename,
                                'slug'      => 'c_' . $getFilename,
                                'slug_ext'  => $converted_filename_ext,
                                'file_size' => $fileSize,
                                'mime_type' => 'video/mp4',
                            ];
                            File::delete(public_path($upload_path.'/'.$slug_ext));
                            // Get the video dimensions
                            $dimension = $video->getStreams()->videos()->first()->getDimensions();

                            // Get the frame rate
                            $frameRate = $video->getStreams()->videos()->first()->get('avg_frame_rate');

                            // Calculate the frame rate
                            $frameRate = floatval(str_replace('/', '/', $frameRate));

                            if($frameRate > 1000) {
                                $frameRate = $frameRate/1000;
                            }
                            if($request->input('story') && !$request->input('challenge_id')) {
                                // Check if the dimensions and frame rate meet the requirements
                                if($frameRate < 30) {
                                    //return ['error' => 'Минимальная частота кадров — 30 кадров/с. Ваше видео: '.$frameRate.'  кадров/с'];
                                }
                                if ($dimension->getHeight() >= 720 && $dimension->getWidth() >= 720) {

                                } else {
//                                    return ['error' => 'Минимальное разрешение — 720 пикселей.'];
                                }
                            }

                        } catch (\Throwable $e) {
                            // Handle exceptions, such as if the file is not a valid video
                            return ['error' => $e->getMessage()];
                        }
                    }

                    $is_uploaded     = Media::create($uploaded_data);
                    $uploadedFiles[] = $is_uploaded;
                }

            } catch (\Exception $e) {
                $errorMsg = $e->getMessage();
                return ['success' => false, 'msg' => $errorMsg];
            }

            return ['success' => true, 'msg' => __('app.media_uploaded'), 'images' => $uploadedFiles];
        }

        if ($request->hasFile('image')) {
            return $this->storeOneFile($request->file('image'), $allowed_file_types);
        }
    }

    /** @return array */
    private function storeOneFile(UploadedFile $file, array $allowed_file_types): array
    {
        $getFilename = $file->getClientOriginalName();
        $clientExt   = $file->getClientOriginalExtension();

//        if (!\in_array($clientExt, $allowed_file_types)) {
//            return ['success' => false, 'msg' => $clientExt . ' - ' . __('app.file_types_not_allowed')];
//        }

        $ext = '.' . $clientExt;

        $baseSlug = str_replace($ext, '', $getFilename);

        $getMimeType = $file->getClientMimeType();
        $getSize     = $file->getSize();

        $slug     = strtolower($baseSlug);
        $slug     = unique_slug($slug, 'Media');
        $slug_ext = $slug . $ext;

        if ('image' == substr($getMimeType, 0, 5)) {
            // It's imgae file
            $image       = $file;
            $image_sizes = config('media.size');
            $upload_dir  = 'uploads/images/';

            foreach ($image_sizes as $ikey => $ivalue) {
                $img_thumb_name = $upload_dir . $ikey . '/' . $slug_ext;

                $resized = Image::make($image)->resize($ivalue[0], $ivalue[1], function($constraint): void {
                    $constraint->upsize();
                })->stream();
                // upload thumb image
                current_disk()->put($img_thumb_name, $resized->__toString(), 'public');
            }
            current_disk()->putFileAs('uploads/images/', $file, $slug_ext, 'public');
        } else {
            current_disk()->putFileAs('uploads/', $file, $slug_ext, 'public');
        }

        $uploaded_data = [
            'user_id'   => Auth::id(),
            'name'      => $getFilename,
            'slug'      => $slug,
            'slug_ext'  => $slug_ext,
            'file_size' => $getSize,
            'mime_type' => $getMimeType,
        ];
        $uf=Media::create($uploaded_data);

        return ['images'=>[$uf]];
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
