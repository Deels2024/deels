<?php

namespace App\Console\Commands;

use App\Models\Campaign;
use App\Models\Story;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\SitemapIndex;
use Spatie\Sitemap\Tags\Url;

class RobotMessagesClear extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'messages:robot:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically clear old robot messages';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $now = Carbon::now()->subDays(7)->format('Y-m-d 00:00:00');
        DB::table('messages')->where('user_id', 0)->where('created_at', '<=',$now)->delete();
    }
}
