<?php

namespace App\Observers;

use App\Models\Article;

class ArticleObserver
{
    /**
     * Handle the Article "created" event.
     */
    public function created(Article $article): void
    {
        if (!str_starts_with($article->slug, (string) $article->id)) {
            $article->slug = "{$article->id}-{$article->slug}";
            $article->saveQuietly(); // saveQuietly avoids triggering another observer event
        }
    }
}
