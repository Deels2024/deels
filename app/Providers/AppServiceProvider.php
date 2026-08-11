<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Category;
use App\Models\Post;
use App\Services\ProxyGuzzleHttpClient;
use Illuminate\Pagination\Paginator;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Telegram\Bot\Api;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(UrlGenerator $url): void
    {
        Paginator::useBootstrap();

        $this->app['request']->server->set('HTTPS', 'on');

        if (App::environment('prod') || str_contains(request()?->getHost(), 'new.')) {
            URL::forceScheme('https');
            $url->forceScheme('https');
        }

        $mainPath = database_path('migrations');
        $directories = glob($mainPath . '/*' , GLOB_ONLYDIR);
        $paths = array_merge([$mainPath], $directories);

        $this->loadMigrationsFrom($paths);

        if (App::environment('testing')) {
            return;
        }

        $header_menu_pages = Cache::remember(
            'header_posts',
            120,
            fn() => Post::whereStatus(1)->where('show_in_header_menu', 1)->get()
        );

        $categories = Cache::remember(
            'categories',
            500,
            fn() => Category::withCount('campaigns')->get()
        );

        setlocale(LC_TIME, 'ru_RU.UTF-8');

        View::share([
            'categories'        => $categories,
            'header_menu_pages' => $header_menu_pages,
        ]);
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ProxyGuzzleHttpClient::class, static fn (): ProxyGuzzleHttpClient => new ProxyGuzzleHttpClient());

        // Telegram SDK expects an HttpClientInterface instance, not a class-string.
        config(['telegram.http_client_handler' => $this->app->make(ProxyGuzzleHttpClient::class)]);

        $this->app->resolving(Api::class, static function (Api $telegram, $app): void {
            $telegram->setHttpClientHandler($app->make(ProxyGuzzleHttpClient::class));
        });
    }
}
