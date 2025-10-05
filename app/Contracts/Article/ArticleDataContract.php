<?php

namespace App\Contracts\Article;

use Carbon\Carbon;

interface ArticleDataContract
{
    public function getProviderKey(): string;

    public function getTitle(): string;

    public function getContent(): string;

    public function getLink(): string;

    public function getPublishedAt(): Carbon;
}
