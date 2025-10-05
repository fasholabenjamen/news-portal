<?php

namespace App\Traits;

use App\Contracts\Article\{
    ArticleDataContract,
    HasDescription,
    HasKeywords,
    HasLanguage,
    HasProviderIdentity,
    HasAuthorName,
    HasCategory,
    HasSource,
    HasImageUrl,
};
use App\Models\Article;
use App\Models\Source;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

trait ArticleSaver
{
    public function saveArticles(Collection $articles): void
    {
        $articles->each(function (ArticleDataContract $article) {
            try {
                $this->saveArticle($article);
            } catch (\Exception $e) {
                // Log the error or handle it as needed
               Log::error('Failed to save article: ' . $e->getMessage());
            }
        });
    }

    public function saveArticle(ArticleDataContract $article): void
    {
        if ($article instanceof HasProviderIdentity) {
            $provider_id = $article->getProviderID();
        } else {
            $provider_id = $this->generateSlug($article->getTitle());
        }
        $article_model = Article::firstOrNew([
            'provider' => $article->getProviderKey(),
            'provider_id' => $provider_id,
        ]);
        if (!$article_model->exists) {
            $article_model->slug = $this->generateSlug($article->getTitle());
        }
        $article_model->title = $article->getTitle();
        $article_model->content = $article->getContent();
        $article_model->link = $article->getLink();
        $article_model->published_at = $article->getPublishedAt();
        if ($article instanceof HasDescription) {
            $article_model->description = $article->getDesciption();
        }
        if ($article instanceof HasKeywords) {
            $article_model->keywords = $article->getKeywords();
        }
        if ($article instanceof HasLanguage) {
            $article_model->language = $article->getLanguage();
        }
        if ($article instanceof HasAuthorName) {
            $article_model->author = $article->getAuthorName();
        }
        if ($article instanceof HasCategory) {
            $article_model->category = $article->getCategory();
        }
        if ($article instanceof HasImageUrl) {
            $article_model->image_url = $article->getImageUrl();
        }
        if ($article instanceof HasSource) {
            $source = Source::firstOrCreate(
                ['key' => $article->getSourceKey()],
                ['label' => $article->getSourceName()]
            );
            $article_model->source_id = $source->id;
        }

        $article_model->save();
    }

    protected function generateSlug(string $title): string
    {
        return Str::slug($title);
    }
}
