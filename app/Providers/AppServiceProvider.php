<?php

namespace App\Providers;

use App\Models\Article;
use App\Observers\ArticleObserver;
use App\Services\Articles\Providers\NewsApiDotAi\ClientConnection as NewsApiDotAiClientConnection;
use App\Services\Articles\Providers\NewsApiDotAi\NewsApiDotAiProvider;
use App\Services\Articles\Providers\NewsApiDotOrg\ClientConnection as NewsApiDotOrgClientConnection;
use App\Services\Articles\Providers\NewsApiDotOrg\NewsApiDotOrgProvider;
use App\Services\Articles\Providers\NewsData\ClientConnection as NewsDataClientConnection;
use App\Services\Articles\Providers\NewsData\NewsDataProvider;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(NewsApiDotOrgProvider::class, function ($app) {
            return new NewsApiDotOrgProvider(new NewsApiDotOrgClientConnection());
        });
        $this->app->singleton(NewsApiDotAiProvider::class, function ($app) {
            return new NewsApiDotAiProvider(new NewsApiDotAiClientConnection());
        });
        $this->app->singleton(NewsDataProvider::class, function ($app) {
            return new NewsDataProvider(new NewsDataClientConnection());
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Article::observe(ArticleObserver::class);
    }
}
