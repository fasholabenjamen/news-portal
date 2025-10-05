<?php

namespace App\Jobs;

use App\Services\Articles\ArticleProviderFactory;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class FetchArticlesJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(protected string $provider_key)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $provider = ArticleProviderFactory::getProvider($this->provider_key);

        if (!$provider) {
            Log::info("Invalid provider key: {$this->provider_key}");
        }
        $provider->fetchAndStoreArticles();
        Log::info('Articles fetched successfully');
    }
}
