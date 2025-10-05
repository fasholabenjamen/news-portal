<?php

namespace App\Services\Articles\Providers;

use App\Contracts\Provider\ArticleProvider;
use App\Traits\ArticleSaver;
use Illuminate\Support\Collection;

abstract class BaseProvider implements ArticleProvider
{
    use ArticleSaver;

    abstract public function fetchAndStoreArticles(): void;

    abstract protected function processData(Collection $data): void;
}
