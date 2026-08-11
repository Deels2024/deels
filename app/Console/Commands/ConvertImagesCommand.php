<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Intervention\Image\Facades\Image;

class ConvertImagesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:convert-images';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        foreach ([
            '/dist/images/kpromo_images/kpromo-img-xl.png',
//            '/dist/images/background-xs.jpg',
//            '/dist/images/kpromo_images/kpromo-img-xs.png',
//            '/uploads/images/fa8c336e-618a-4a4c-9d64-8d42543162cd.jpeg',
//            '/dist/images/index/reason.png',
//            '/images/action-top-banner/start.png',
//            '/dist/images/index/start.png',
//            '/dist/images/kpromo_images/kpromo-bg.jpg',
//            '/images/action-top-banner/startmob.png',
//            '/dist/images/kpromo_images/prslide6.jpg',
//            '/uploads/images/196cc27f-011a-41b8-b000-9f8ef19d418c.jpeg',
//            '/dist/images/kpromo_images/prslide9.jpg',
//            '/dist/images/kpromo_images/prslide7.jpg',
//            '/default_avatars/avatar_6.png',
//            '/default_avatars/avatar_1.png',
//            '/default_avatars/avatar_2.png',
//            '/default_avatars/avatar_3.png',
//            '/default_avatars/avatar_4.png',
//            '/default_avatars/avatar_5.png',
        ] as $item) {
            Image::make(public_path($item))
                 ->encode('webp', 100)
                 ->save(public_path(str_replace(['.png', '.jpg', '.jpeg'], '.webp', $item)));
        }
        //        Campaign::query()
        //                ->latest()
        //                ->each(function(Campaign $campaign) {
        //                    if ($campaign->feature_img_url()->thumbnail) {
        //                        try {
        //                            Image::make($campaign->feature_img_url()->thumbnail)
        //                                 ->encode('webp', 100)
        //                                 ->save(public_path("uploads/webp/thumbs/{$campaign->slug}.webp"));
        //
        //                            dump($campaign->slug);
        //                        } catch (\Exception $exception) {
        //                            dump('err');
        //                        }
        //                    }
        //                });
    }
}
