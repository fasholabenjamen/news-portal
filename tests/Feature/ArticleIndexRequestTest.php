<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use App\Models\Source;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ArticleIndexRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_endpoint_returns_expected_data_with_filters(): void
    {
        $categoryOne = Category::create([
            'key' => 'category-1',
            'label' => 'Category 1',
        ]);

        $categoryTwo = Category::create([
            'key' => 'category-2',
            'label' => 'Category 2',
        ]);

        $authorOne = Author::create([
            'key' => 'author-1',
            'label' => 'Author 1',
        ]);

        $authorTwo = Author::create([
            'key' => 'author-2',
            'label' => 'Author 2',
        ]);

        $sourceOne = Source::create([
            'key' => 'source-1',
            'label' => 'Source 1',
        ]);

        $sourceTwo = Source::create([
            'key' => 'source-2',
            'label' => 'Source 2',
        ]);

        $articleOne = Article::create([
            'slug' => Str::slug('Article One ' . Str::uuid()),
            'provider_id' => Str::uuid()->toString(),
            'provider' => 'news_api_dot_org',
            'title' => 'Article One',
            'description' => 'First article description',
            'content' => 'First article content',
            'image_url' => null,
            'source_id' => $sourceOne->id,
            'author_id' => $authorOne->id,
            'category_id' => $categoryOne->id,
            'link' => 'https://example.com/article-one',
            'published_at' => now(),
            'language' => 'en',
            'keywords' => 'news,example',
        ]);

        $articleTwo = Article::create([
            'slug' => Str::slug('Article Two ' . Str::uuid()),
            'provider_id' => Str::uuid()->toString(),
            'provider' => 'news_api_dot_org',
            'title' => 'Article Two',
            'description' => 'Second article description',
            'content' => 'Second article content',
            'image_url' => null,
            'source_id' => $sourceTwo->id,
            'author_id' => $authorTwo->id,
            'category_id' => $categoryTwo->id,
            'link' => 'https://example.com/article-two',
            'published_at' => now(),
            'language' => 'en',
            'keywords' => 'news,example',
        ]);

        $articleThree = Article::create([
            'slug' => Str::slug('Article Three ' . Str::uuid()),
            'provider_id' => Str::uuid()->toString(),
            'provider' => 'news_api_dot_org',
            'title' => 'Article Three',
            'description' => 'Third article description',
            'content' => 'Third article content',
            'image_url' => null,
            'source_id' => $sourceOne->id,
            'author_id' => $authorOne->id,
            'category_id' => $categoryTwo->id,
            'link' => 'https://example.com/article-three',
            'published_at' => now(),
            'language' => 'en',
            'keywords' => 'news,example',
        ]);

        $response = $this->getJson(route('articles.index', [
            'categories_id' => $categoryOne->id . ',' . $categoryTwo->id,
            'authors_id' => (string) $authorOne->id,
            'sources_id' => $sourceOne->id . ',' . $sourceTwo->id,
            'per_page' => 50,
        ]));

        $response->assertOk();

        $returnedIds = collect($response->json('data'))->pluck('id')->all();

        $this->assertContains($articleOne->id, $returnedIds);
        $this->assertContains($articleThree->id, $returnedIds);
        $this->assertNotContains($articleTwo->id, $returnedIds);
    }
}
