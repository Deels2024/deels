<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Media;
use FFMpeg\FFMpeg;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Intervention\Image\ImageManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MediaUploadService
{
    private const STORY_IMAGE_FORMAT_ERROR = 'Этот формат не поддерживается. Пожалуйста, загрузите фото в формате JPEG, JPG, PNG, HEIF или HEIC';

    public function storeApiVideo(Request $request): ?Media
    {
        if (!$request->hasFile('video')) {
            return null;
        }

        $file = $request->file('video');
        $path = 'uploads/stories/';
        $filenameWithExt = $file->getClientOriginalName();
        $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
        $extension = 'webm';
        $fileNameToStore = preg_replace('/\s+/', '_', $filename . '_' . time() . '.' . $extension);

        $uploadPath = '/uploads/stories';
        if (!File::isDirectory($uploadPath)) {
            File::makeDirectory($uploadPath, 0777, true, true);
        }

        Storage::disk('public')->putFileAs($path, $file, $fileNameToStore);

        return Media::create([
            'user_id' => Auth::user()->id,
            'name' => $filename,
            'slug' => $filename,
            'slug_ext' => $filenameWithExt,
            'file_size' => 0,
            'mime_type' => 'video/mp4',
        ]);
    }

    public function store(Request $request, string $path = 'challenges'): array
    {
        $user_id = Auth::user()->id ?? $request->input('user_id');
        $allowed_file_types = config('app.allowed_files');
        $story = $request->input('story');
        $challenge = $request->input('challenge') ?? $request->input('battle');

        $upload_dir = 'uploads/images/';
        $upload_path = 'uploads/images/';
        if ($story) {
            $stories_path = '/uploads/stories/' . date('Y/m/d');
            $upload_dir = $stories_path . '/thumbs/';
            $upload_path = $stories_path;
        }
        if ($challenge) {
            $challenges_path = '/uploads/' . $path . '/' . date('Y/m/d');
            $upload_dir = $challenges_path . '/thumbs/';
            $upload_path = $challenges_path;
        }

        if ($request->file('video')) {
            $getFilename = md5(microtime());
            $filename = $getFilename . '.mp4';
            $target_file = public_path($upload_path . '/' . $filename);
            $converted_filename_ext = 'c_' . $filename;
            $converted_upload_path = 'uploads/stories';
            $videoUrl_converted = public_path($converted_upload_path . '/' . $converted_filename_ext);
            File::ensureDirectoryExists(public_path($upload_path));
            File::ensureDirectoryExists(public_path($converted_upload_path));
            move_uploaded_file(
                $_FILES['video']['tmp_name'],
                $target_file
            );

            $ffmpeg = FFMpeg::create([
                'ffmpeg.binaries' => env('FFMPEG'),
                'ffprobe.binaries' => env('FFPROBE'),
            ]);
            $video = $ffmpeg->open($target_file);
            $format = new \FFMpeg\Format\Video\X264('aac', 'libx264');
            $format->setKiloBitrate(20000);
            $format->setAdditionalParameters([
                '-preset', 'slow',
                '-crf', '18',
                '-pix_fmt', 'yuv420p',
            ]);
            $video->save($format, $videoUrl_converted);
            File::delete($target_file);
            $uploaded_data = [
                'user_id' => $user_id,
                'name' => 'c_' . $getFilename,
                'folder' => $converted_upload_path,
                'slug' => 'c_' . $getFilename,
                'slug_ext' => $converted_filename_ext,
                'path' => $converted_upload_path . '/' . $converted_filename_ext,
                'file_size' => '0',
                'mime_type' => 'video/mp4',
            ];
            $is_uploaded = Media::create($uploaded_data);
            $uploadedFiles[] = $is_uploaded;

            return ['success' => true, 'msg' => __('app.media_uploaded'), 'images' => $uploadedFiles];
        }

        if ($request->files->has('files')) {
            $files = $request->files->get('files');
            $uploadedFiles = [];

            try {
                foreach ($files as $file) {
                    $getFilename = $file->getClientOriginalName();
                    $clientExt = strtolower($file->getClientOriginalExtension());
                    $getFilename = md5(microtime()) . '.' . $clientExt;

                    $ext = '.' . $clientExt;

                    $baseSlug = str_replace($ext, '', $getFilename);

                    $getMimeType = $file->getClientMimeType();
                    $getSize = $file->getSize();

                    $slug = strtolower($baseSlug);
                    $slug = unique_slug($slug, 'Media');
                    $slug_ext = $slug . $ext;

                    if ($story && $this->isStoryImageFile($file)) {
                        $validationError = $this->validateStoryImage($file);
                        if ($validationError) {
                            return ['success' => false, 'error' => $validationError, 'msg' => $validationError];
                        }

                        $slug_ext = $slug . '.jpg';
                        $webp_slug_ext = $slug . '.webp';

                        try {
                            $image = $this->makeStoryImage($file)
                                ->orientate()
                                ->resize(
                                    config('media.stories.image_max_width'),
                                    config('media.stories.image_max_height'),
                                    function ($constraint): void {
                                        $constraint->aspectRatio();
                                        $constraint->upsize();
                                    }
                                );

                            current_disk()->put(
                                $upload_path . '/' . $slug_ext,
                                (string) $image->encode('jpg', 90),
                                'public'
                            );
                            current_disk()->put(
                                $upload_path . '/' . $webp_slug_ext,
                                (string) $image->encode('webp', 85),
                                'public'
                            );

                            $uploaded_data = [
                                'user_id' => $user_id,
                                'name' => $getFilename,
                                'folder' => $upload_path,
                                'slug' => $slug,
                                'slug_ext' => $slug_ext,
                                'path' => $upload_path . '/' . $webp_slug_ext,
                                'file_size' => $getSize,
                                'mime_type' => 'image/jpeg',
                            ];
                        } catch (\Throwable $e) {
                            Log::error('Ошибка обработки изображения сторис: ' . $e->getMessage());

                            return ['success' => false, 'error' => self::STORY_IMAGE_FORMAT_ERROR, 'msg' => self::STORY_IMAGE_FORMAT_ERROR];
                        }
                    } elseif ('image' == substr($getMimeType, 0, 5)) {
                        $image = $file;
                        $image_sizes = config('media.size');

                        foreach ($image_sizes as $ikey => $ivalue) {
                            $img_thumb_name = $upload_dir . $ikey . '/' . $slug_ext;

                            $resized = Image::make($image)
                                ->orientate()
                                ->resize($ivalue[0], $ivalue[1], function ($constraint): void {
                                    $constraint->aspectRatio();
                                })
                                ->encode('webp', 100)
                                ->stream();
                            current_disk()->put($img_thumb_name, $resized->__toString(), 'public');
                        }
                        current_disk()->putFileAs($upload_path, $file, $slug_ext, 'public');
                        $uploaded_data = [
                            'user_id' => $user_id,
                            'name' => $getFilename,
                            'folder' => $upload_path,
                            'slug' => $slug,
                            'slug_ext' => $slug_ext,
                            'file_size' => $getSize,
                            'mime_type' => $getMimeType,
                        ];
                    } else {
                        $upload_path = 'uploads/';
                        if ($story) {
                            $stories_path = '/uploads/stories/' . date('Y/m/d');
                            $upload_dir = $stories_path . '/thumbs/';
                            $upload_path = $stories_path;
                            if (!File::isDirectory($upload_path)) {
                                File::makeDirectory($upload_path, 0777, true, true);
                            }
                        }
                        if ($challenge) {
                            $challenges_path = '/uploads/' . $path . '/' . date('Y/m/d');
                            $upload_dir = $challenges_path . '/thumbs/';
                            $upload_path = $challenges_path;
                            if (!File::isDirectory($upload_path)) {
                                File::makeDirectory($upload_path, 0777, true, true);
                            }
                        }

                        try {
                            current_disk()->putFileAs($upload_path, $file, $slug_ext, 'public');
                            $original_path = public_path($upload_path . '/' . $slug_ext);
                            $fileSize = filesize($original_path);
                            $maxVideoSeconds = (int) $request->input('max_video_seconds');
                            if ($maxVideoSeconds > 0) {
                                $convertedSlugExt = 'c_' . $slug . '.mp4';
                                $convertedPath = public_path($upload_path . '/' . $convertedSlugExt);
                                $ffmpeg = FFMpeg::create([
                                    'ffmpeg.binaries' => env('FFMPEG'),
                                    'ffprobe.binaries' => env('FFPROBE'),
                                ]);
                                $video = $ffmpeg->open($original_path);
                                $format = new \FFMpeg\Format\Video\X264('aac', 'libx264');
                                $format->setKiloBitrate(20000);
                                $format->setAdditionalParameters([
                                    '-preset', 'slow',
                                    '-crf', '18',
                                    '-pix_fmt', 'yuv420p',
                                    '-movflags', '+faststart',
                                    '-vf', 'scale=720:1280:force_original_aspect_ratio=decrease,pad=720:1280:(ow-iw)/2:(oh-ih)/2',
                                    '-t', (string) $maxVideoSeconds,
                                ]);
                                $video->save($format, $convertedPath);
                                File::delete($original_path);
                                $slug_ext = $convertedSlugExt;
                                $slug = str_replace('.mp4', '', $convertedSlugExt);
                                $getFilename = $convertedSlugExt;
                                $fileSize = filesize($convertedPath);
                            }
                            $uploaded_data = [
                                'user_id' => $user_id,
                                'name' => $getFilename,
                                'slug' => $slug,
                                'folder' => $upload_path,
                                'slug_ext' => $slug_ext,
                                'file_size' => $fileSize,
                                'mime_type' => 'video/mp4',
                            ];
                        } catch (\Throwable $e) {
                        }
                    }

                    $is_uploaded = Media::create($uploaded_data);
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

        return [];
    }

    private function storeOneFile(UploadedFile $file, array $allowed_file_types): array
    {
        $getFilename = $file->getClientOriginalName();
        $clientExt = $file->getClientOriginalExtension();

        $ext = '.' . $clientExt;

        $baseSlug = str_replace($ext, '', $getFilename);

        $getMimeType = $file->getClientMimeType();
        $getSize = $file->getSize();

        $slug = strtolower($baseSlug);
        $slug = unique_slug($slug, 'Media');
        $slug_ext = $slug . $ext;

        if ('image' == substr($getMimeType, 0, 5)) {
            $image = $file;
            $image_sizes = config('media.size');
            $upload_dir = 'uploads/images/';

            foreach ($image_sizes as $ikey => $ivalue) {
                $img_thumb_name = $upload_dir . $ikey . '/' . $slug_ext;

                $resized = Image::make($image)->resize($ivalue[0], $ivalue[1], function ($constraint): void {
                    $constraint->upsize();
                })->stream();
                current_disk()->put($img_thumb_name, $resized->__toString(), 'public');
            }
            current_disk()->putFileAs('uploads/images/', $file, $slug_ext, 'public');
        } else {
            current_disk()->putFileAs('uploads/', $file, $slug_ext, 'public');
        }

        $uploaded_data = [
            'user_id' => Auth::id(),
            'name' => $getFilename,
            'slug' => $slug,
            'slug_ext' => $slug_ext,
            'file_size' => $getSize,
            'mime_type' => $getMimeType,
        ];
        $uf = Media::create($uploaded_data);

        return ['images' => [$uf]];
    }

    private function validateStoryImage(UploadedFile $file): ?string
    {
        $extension = strtolower($file->getClientOriginalExtension());

        if (in_array($extension, config('media.stories.image_unsupported_extensions', []), true)) {
            return self::STORY_IMAGE_FORMAT_ERROR;
        }

        if (!in_array($extension, config('media.stories.image_allowed_extensions', []), true)) {
            return self::STORY_IMAGE_FORMAT_ERROR;
        }

        $maxMb = (int) config('media.stories.image_max_upload_mb');
        if ($file->getSize() > $maxMb * 1024 * 1024) {
            return "Вес файла превышает {$maxMb} Мб. Пожалуйста, загрузите файл до {$maxMb} Мб";
        }

        return null;
    }

    private function isStoryImageFile(UploadedFile $file): bool
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $mimeType = $file->getClientMimeType();

        return substr($mimeType, 0, 5) === 'image'
            || in_array($extension, config('media.stories.image_allowed_extensions', []), true)
            || in_array($extension, config('media.stories.image_unsupported_extensions', []), true);
    }

    private function makeStoryImage(UploadedFile $file)
    {
        $extension = strtolower($file->getClientOriginalExtension());

        if (in_array($extension, ['heic', 'heif'], true) && class_exists(\Imagick::class)) {
            return (new ImageManager(['driver' => 'imagick']))->make($file);
        }

        return Image::make($file);
    }
}
