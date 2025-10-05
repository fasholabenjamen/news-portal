<?php

namespace App\Console\Commands;

use App\Enums\ArticleProviders;
use App\Jobs\FetchArticlesJob;
use App\Services\Articles\ArticleProviderFactory;
use Illuminate\Console\Command;

class FetchArticles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-articles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch articles from all providers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        collect(ArticleProviderFactory::getProviders())->each(function (ArticleProviders $provider) {
            FetchArticlesJob::dispatch($provider->value);
        });
    }
}
