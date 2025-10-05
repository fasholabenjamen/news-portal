<?php

namespace Tests\Feature;

use App\Helpers\ClientResponse;
use App\Models\Article;
use App\Services\Articles\Providers\NewsData\NewsDataProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\MockClientConnection;
use Tests\TestCase;

class NewsDataProviderTest extends TestCase
{
    use RefreshDatabase;

    private MockClientConnection $mockConnection;
    private NewsDataProvider $provider;

    protected function setUp(): void
    {
        parent::setUp();
        
        config(['services.news_data.max_page' => 3]);
        
        $this->mockConnection = new MockClientConnection();
        $this->provider = new NewsDataProvider($this->mockConnection);
    }

    public function test_fetch_and_store_articles_successfully(): void
    {
        $this->mockConnection->setMockResponses([
            new ClientResponse(200, null, [
                'results' => [
                    [
                        'article_id' => 'article-1',
                        'title' => 'Test NewsData Article 1',
                        'content' => 'Content 1',
                        'link' => 'https://example.com/article1',
                        'pubDate' => '2024-01-01 12:00:00',
                        'image_url' => 'https://example.com/image1.jpg',
                        'language' => 'en',
                        'description' => 'Description 1',
                        'source_id' => 'source-1',
                        'source_name' => 'Source 1',
                        'category' => ['technology', 'business'],
                        'keywords' => ['tech', 'innovation']
                    ]
                ],
                'nextPage' => 'next-page-token'
            ]),
            new ClientResponse(200, null, [
                'results' => [
                    [
                        'article_id' => 'article-2',
                        'title' => 'Test NewsData Article 2',
                        'content' => 'Content 2',
                        'link' => 'https://example.com/article2',
                        'pubDate' => '2024-01-02 12:00:00',
                        'image_url' => null,
                        'language' => 'en',
                        'description' => 'Description 2',
                        'source_id' => 'source-2',
                        'source_name' => 'Source 2',
                        'category' => ['top', 'science'],
                        'keywords' => []
                    ]
                ],
                'nextPage' => null
            ])
        ]);

        $this->provider->fetchAndStoreArticles();

        $this->assertDatabaseCount('articles', 2);
        
        $this->assertDatabaseHas('articles', [
            'title' => 'Test NewsData Article 1',
            'provider' => 'news_data',
            'provider_id' => 'article-1',
            'keywords' => 'tech,innovation',
        ]);

        $this->assertDatabaseHas('articles', [
            'title' => 'Test NewsData Article 2',
            'provider' => 'news_data',
            'provider_id' => 'article-2',
            'keywords' => '',
        ]);

        $firstArticle = Article::with('category')
            ->where('provider_id', 'article-1')
            ->first();
        $this->assertNotNull($firstArticle?->category);
        $this->assertEquals('technology business', $firstArticle->category->label);

        $secondArticle = Article::with('category')
            ->where('provider_id', 'article-2')
            ->first();
        $this->assertNotNull($secondArticle?->category);
        $this->assertEquals('science', $secondArticle->category->label);

        $this->assertEquals(2, $this->mockConnection->getCallCount());
    }

    public function test_fetch_and_store_articles_handles_failed_request(): void
    {
        $this->mockConnection->setMockResponse(500, 'Server Error', []);

        $this->provider->fetchAndStoreArticles();

        $this->assertDatabaseCount('articles', 0);
        $this->assertEquals(1, $this->mockConnection->getCallCount());
    }

    public function test_fetch_and_store_articles_handles_empty_results(): void
    {
        $this->mockConnection->setMockResponse(200, null, [
            'results' => [],
            'nextPage' => null
        ]);

        $this->provider->fetchAndStoreArticles();

        $this->assertDatabaseCount('articles', 0);
    }

    public function test_fetch_and_store_articles_stops_at_max_pages(): void
    {
        config(['services.news_data.max_page' => 2]);

        $response = new ClientResponse(200, null, [
            'results' => [
                [
                    'article_id' => 'article-1',
                    'title' => 'Test Article',
                    'content' => 'Content',
                    'link' => 'https://example.com/article',
                    'pubDate' => '2024-01-01 12:00:00',
                    'image_url' => null,
                    'language' => 'en',
                    'description' => 'Description',
                    'source_id' => 'source-1',
                    'source_name' => 'Source 1',
                    'category' => ['technology'],
                    'keywords' => []
                ]
            ],
            'nextPage' => 'next-page-token'
        ]);

        $this->mockConnection->setMockResponse(200, null, $response->data);

        $this->provider->fetchAndStoreArticles();

        $this->assertDatabaseCount('articles', 1);
    }

    public function test_fetch_and_store_articles_stops_when_no_next_page(): void
    {
        $this->mockConnection->setMockResponse(200, null, [
            'results' => [
                [
                    'article_id' => 'article-1',
                    'title' => 'Test Article',
                    'content' => 'Content',
                    'link' => 'https://example.com/article',
                    'pubDate' => '2024-01-01 12:00:00',
                    'image_url' => null,
                    'language' => 'en',
                    'description' => 'Description',
                    'source_id' => 'source-1',
                    'source_name' => 'Source 1',
                    'category' => ['technology'],
                    'keywords' => []
                ]
            ],
            'nextPage' => null
        ]);

        $this->provider->fetchAndStoreArticles();

        $this->assertDatabaseCount('articles', 1);
        $this->assertEquals(1, $this->mockConnection->getCallCount());
    }

    public function test_fetch_and_store_articles_with_string_category(): void
    {
        $this->mockConnection->setMockResponse(200, null, [
            'results' => [
                [
                    'article_id' => 'article-1',
                    'title' => 'Test Article',
                    'content' => 'Content',
                    'link' => 'https://example.com/article',
                    'pubDate' => '2024-01-01 12:00:00',
                    'image_url' => null,
                    'language' => 'en',
                    'description' => 'Description',
                    'source_id' => 'source-1',
                    'source_name' => 'Source 1',
                    'category' => 'technology',
                    'keywords' => []
                ]
            ],
            'nextPage' => null
        ]);

        $this->provider->fetchAndStoreArticles();

        $article = Article::with('category')->where('title', 'Test Article')->first();
        $this->assertNotNull($article?->category);
        $this->assertEquals('technology', $article->category->label);
    }

    public function test_fetch_and_store_articles_filters_top_from_categories(): void
    {
        $this->mockConnection->setMockResponse(200, null, [
            'results' => [
                [
                    'article_id' => 'article-1',
                    'title' => 'Test Article',
                    'content' => 'Content',
                    'link' => 'https://example.com/article',
                    'pubDate' => '2024-01-01 12:00:00',
                    'image_url' => null,
                    'language' => 'en',
                    'description' => 'Description',
                    'source_id' => 'source-1',
                    'source_name' => 'Source 1',
                    'category' => ['top', 'technology', 'business'],
                    'keywords' => []
                ]
            ],
            'nextPage' => null
        ]);

        $this->provider->fetchAndStoreArticles();

        $article = Article::with('category')->where('title', 'Test Article')->first();
        $this->assertNotNull($article?->category);
        $this->assertEquals('technology business', $article->category->label);
    }

    public function test_fetch_and_store_articles_creates_source(): void
    {
        $this->mockConnection->setMockResponse(200, null, [
            'results' => [
                [
                    'article_id' => 'article-1',
                    'title' => 'Test Article',
                    'content' => 'Content',
                    'link' => 'https://example.com/article',
                    'pubDate' => '2024-01-01 12:00:00',
                    'image_url' => null,
                    'language' => 'en',
                    'description' => 'Description',
                    'source_id' => 'unique-source-id',
                    'source_name' => 'Unique Source Name',
                    'category' => ['technology'],
                    'keywords' => []
                ]
            ],
            'nextPage' => null
        ]);

        $this->provider->fetchAndStoreArticles();

        $this->assertDatabaseHas('sources', [
            'key' => 'unique-source-id',
            'label' => 'Unique Source Name',
        ]);
    }

    public function test_fetch_and_store_articles_updates_existing_article(): void
    {
        Article::create([
            'provider' => 'news_data',
            'provider_id' => 'article-1',
            'title' => 'Old Title',
            'slug' => 'old-title',
            'content' => 'Old Content',
            'link' => 'https://example.com/old',
            'published_at' => now()->subDay(),
        ]);

        $this->mockConnection->setMockResponse(200, null, [
            'results' => [
                [
                    'article_id' => 'article-1',
                    'title' => 'Updated Title',
                    'content' => 'Updated Content',
                    'link' => 'https://example.com/updated',
                    'pubDate' => '2024-01-01 12:00:00',
                    'image_url' => null,
                    'language' => 'en',
                    'description' => 'Updated Description',
                    'source_id' => 'source-1',
                    'source_name' => 'Source 1',
                    'category' => ['technology'],
                    'keywords' => []
                ]
            ],
            'nextPage' => null
        ]);

        $this->provider->fetchAndStoreArticles();

        $this->assertDatabaseCount('articles', 1);
        $this->assertDatabaseHas('articles', [
            'provider_id' => 'article-1',
            'title' => 'Updated Title',
        ]);
    }

    public function test_fetch_and_store_articles_handles_missing_results_key(): void
    {
        $this->mockConnection->setMockResponse(200, null, [
            'nextPage' => null
        ]);

        $this->provider->fetchAndStoreArticles();

        $this->assertDatabaseCount('articles', 0);
    }
}
