<?php

namespace App\Contracts\Provider;

interface ArticleProvider
{
    public function fetchAndStoreArticles(): void;
}
