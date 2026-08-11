<?php

namespace App\Console\Commands\System;

use App\Helpers\TgHelper;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SitemapGeneration extends Command
{
    use TgHelper;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sitemap:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            // create new sitemap object
            $sitemap = App::make('sitemap');

            $pages = [
                [
                    'url' => url('/'),
                    'priority' => '1.0',
                ],
                [
                    'url' => url('/about-us'),
                    'priority' => '0.8',
                ],
                [
                    'url' => url('/campaigns'),
                    'priority' => '0.8',
                ],
                [
                    'url' => url('/challenges'),
                    'priority' => '0.8',
                ],
                [
                    'url' => url('/stories'),
                    'priority' => '0.8',
                ],
                [
                    'url' => url('/contacts'),
                    'priority' => '0.8',
                ],
            ];

            $items_types = [
                [
                    'table' => 'campaigns',
                    'slug' => 'campaigns',
                    'route' => 'campaign_single',
                    'field' => 'slug',
                ],
                [
                    'table' => 'challenges',
                    'slug' => 'challenges',
                    'route' => 'challenge_page',
                    'field' => 'id',
                ],
                [
                    'table' => 'stories',
                    'slug' => 'stories',
                    'route' => 'stories.get',
                    'field' => 'id',
                ],
            ];


            foreach ($pages as $page) {
                $sitemap->add($page['url'], Carbon::now(), $page['priority'], 'daily');
            }


            $sitemap->store('xml', 'sitemap-pages');
            $sitemap->addSitemap(secure_url('sitemap-pages.xml'));
            foreach ($items_types as $items_type) {
                // get all products from db (or wherever you store them)
                $items_table = $items_type['table'];
                $items_slug = $items_type['slug'];
                $items_route = $items_type['route'];
                $items_field = $items_type['field'];

//                $items = DB::table($items_table)->orderBy('created_at', 'desc')->get();

                // counters
                $counter = 0;
                $sitemapCounter = 0;
                $sitemapCounterSlug = '';

                $items = DB::table($items_table);

                if(in_array($items_table, ['stories', 'challenges'])) {
                    $items->where('active', true)->where('declined', false);
                } else {
                    $items->where('status', 1);
                }
                $items->orderBy('created_at', 'desc')
                    ->chunk(2000, function ($items) use (&$sitemap, &$items_slug, &$items_route, &$items_field, &$counter, &$sitemapCounter, &$sitemapCounterSlug) {
                    // add every product to multiple sitemaps with one sitemap index
                    foreach ($items as $p) {
                        $item_route = route($items_route, $p->{$items_field});
                        if($items_slug == 'stories') {
                            $item_route = url('/stories?show=').$p->id;
                        }
                        if ($sitemapCounter > 0) {
                            $sitemapCounterSlug = '' . $sitemapCounter;
                        }
                        if ($counter == 2000) {
                            // generate new sitemap file
                            $sitemap->store('xml', 'sitemap-' . $items_slug . $sitemapCounterSlug);
                            // add the file to the sitemaps array
                            $sitemap->addSitemap(secure_url('sitemap-' . $items_slug . $sitemapCounterSlug . '.xml'));
                            // reset items array (clear memory)
                            $sitemap->model->resetItems();
                            // reset the counter
                            $counter = 0;
                            // count generated sitemap
                            $sitemapCounter++;
                        }

                        // add product to items array
                        $sitemap->add($item_route, $p->updated_at, '0.6', 'daily');
                        // count number of elements
                        $counter++;
                    }


                });

                // you need to check for unused items
                if (!empty($sitemap->model->getItems())) {
                    // generate sitemap with last items
                    $sitemap->store('xml', 'sitemap-' . $items_slug . $sitemapCounterSlug);
                    // add sitemap to sitemaps array
                    $sitemap->addSitemap(secure_url('sitemap-' . $items_slug . $sitemapCounterSlug . '.xml'));
                    // reset items array
                    $sitemap->model->resetItems();
                }

            }

            // generate new sitemapindex that will contain all generated sitemaps above
            $sitemap->store('sitemapindex', 'sitemap');
//            $this->sendTgMessage('SitemapGeneration success', 190036322);
        } catch (\Throwable $e) {
//            Log::info($e->getMessage());
            $this->sendTgMessage('SitemapGeneration error', 190036322);
            $this->sendTgMessage($e->getMessage(), 190036322);
        }
    }

}
