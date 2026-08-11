<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;

class UserService
{
    public function changeAvatar($originalImage): string
    {
        $image = (new ImageManager())->make($originalImage->getRealPath());
        $image->orientate();

        //$imageName = 'avatar_u' . auth()->id() . '.' . $originalImage->extension();
        $imageName = Str::uuid() . '.' . $originalImage->extension();

        $directory = '/uploads/images/avatars/'.auth()->id().'/';
        File::cleanDirectory(public_path($directory));
        if(!File::isDirectory(public_path($directory))){
            File::makeDirectory(public_path($directory), 0777, true, true);
        }
        if(!File::isDirectory(public_path($directory) . 'large/')){
            File::makeDirectory(public_path($directory) . 'large/', 0777, true, true);
        }



        $smallImage = clone $image;
        $image->save(public_path($directory) . 'large/' . $imageName);
        $resizedImageSmall = $smallImage
            ->fit(130, 130)
            ->save(public_path($directory) . $imageName);

        return $directory . $resizedImageSmall->basename;
    }

    public function uploadCover($originalImage, $story_id): string
    {
        $image = (new ImageManager())->make($originalImage->getRealPath());
        $image->orientate();

        //$imageName = 'avatar_u' . auth()->id() . '.' . $originalImage->extension();
        $imageName = Str::uuid() . '.' . $originalImage->extension();

        $directory = '/uploads/stories/thumbs/story_' . $story_id . '/';
        File::cleanDirectory(public_path($directory));
        if(!File::isDirectory(public_path($directory))){
            File::makeDirectory(public_path($directory), 0777, true, true);
        }

        $smallImage = clone $image;
        $image->save(public_path($directory) . $imageName);
        $resizedImageSmall = $smallImage
//            ->fit(130, 130)
            ->save(public_path($directory) . $imageName);

        return $directory . $resizedImageSmall->basename;
    }
}