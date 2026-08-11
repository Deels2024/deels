<?php

namespace App\Console\Commands;

use App\Models\Campaign;
use App\Models\Story;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\SitemapIndex;
use Spatie\Sitemap\Tags\Url;

class GenerateSitemap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sitemap:generate:old';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically Generate an XML Sitemap';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $postsitemap = Sitemap::create();


        $postsitemap->add(Url::create('/')
        ->setLastModificationDate(Carbon::yesterday())
        ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
        ->setPriority(0.1));

        $postsitemap->add(Url::create(route('browse_campaigns'))
        ->setLastModificationDate(Carbon::yesterday())
        ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
        ->setPriority(0.8));
        $postsitemap->add(Url::create(route('stories.catalog'))
        ->setLastModificationDate(Carbon::yesterday())
        ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
        ->setPriority(0.8));
        $postsitemap->add(Url::create(url('about-us'))
        ->setLastModificationDate(Carbon::yesterday())
        ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
        ->setPriority(0.8));
        $postsitemap->add(Url::create(url('contact-us'))
        ->setLastModificationDate(Carbon::yesterday())
        ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
        ->setPriority(0.8));

        Campaign::select(['slug', 'updated_at'])
            ->active()
            ->chunk(40000, function ($items, $chunk) use ($postsitemap) {
                $sitemapName = 'sitemap_campaigns'.$chunk.'.xml';
                $sitemap = '<?xml version="1.0" encoding="UTF-8"?>';
                $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">';

                foreach ($items as $item) {
                    $sitemap .= '<url>';
                    $sitemap .= '<loc>'.url(route('campaign_single', $item->slug)).'</loc>';
                    $sitemap .= '<lastmod>'.$item->updated_at->format('Y-m-d').'</lastmod>';
                    $sitemap .= '</url>';
                }

                $sitemap .= '</urlset>';

                file_put_contents(public_path($sitemapName), $sitemap);

                $postsitemap->add($sitemapName);
            });

        $postsitemap->writeToFile(public_path('sitemap.xml'));
    }
}
