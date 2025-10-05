<?php

namespace Tests\Feature;

use App\Helpers\ClientResponse;
use App\Models\Article;
use App\Services\Articles\Providers\NewsApiDotOrg\NewsApiDotOrgProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\MockNewsApiDotOrgConnection;
use Tests\TestCase;

class NewsApiDotOrgProviderTest extends TestCase
{
    use RefreshDatabase;

    private MockNewsApiDotOrgConnection $mockConnection;
    private NewsApiDotOrgProvider $provider;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->mockConnection = new MockNewsApiDotOrgConnection();
        $this->provider = new NewsApiDotOrgProvider($this->mockConnection);
    }

    public function test_fetch_and_store_articles_successfully(): void
    {
        $this->mockConnection->setTopHeadlineMockResponse(200, null, [
            'sources' => [
                ['id' => 'bbc-news', 'name' => 'BBC News'],
                ['id' => 'cnn', 'name' => 'CNN'],
            ]
        ]);

        $this->mockConnection->setMockResponses([
            new ClientResponse(200, null, [
                'articles' => [
                    [
                        'title' => 'Test Article 1',
                        'content' => 'Content 1',
                        'url' => 'http://example.com/1',
                        'publishedAt' => '2024-01-01T12:00:00Z',
                        'urlToImage' => 'http://example.com/image1.jpg',
                        'description' => 'Description 1',
                        'author' => 'Author 1',
                        'source' => ['id' => 'bbc-news', 'name' => 'BBC News'],
                    ]
                ]
            ]),
            new ClientResponse(200, null, [
                'articles' => [
                    [
                        'title' => 'Test Article 2',
                        'content' => 'Content 2',
                        'url' => 'http://example.com/2',
                        'publishedAt' => '2024-01-02T12:00:00Z',
                        'urlToImage' => 'http://example.com/image2.jpg',
                        'description' => 'Description 2',
                        'author' => 'Author 2',
                        'source' => ['id' => 'cnn', 'name' => 'CNN'],
                    ]
                ]
            ])
        ]);

        $this->provider->fetchAndStoreArticles();

        $this->assertDatabaseCount('articles', 2);
        $this->assertDatabaseCount('sources', 2);
        
        $this->assertDatabaseHas('articles', [
            'title' => 'Test Article 1',
            'provider' => 'news_api_dot_org',
        ]);

        $this->assertDatabaseHas('articles', [
            'title' => 'Test Article 2',
            'provider' => 'news_api_dot_org',
        ]);

        $this->assertDatabaseHas('sources', [
            'key' => 'bbc-news',
            'label' => 'BBC News',
        ]);
    }

    public function test_fetch_and_store_articles_handles_failed_source_request(): void
    {
        $this->mockConnection->setTopHeadlineMockResponse(500, 'Server Error', []);

        $this->provider->fetchAndStoreArticles();

        $this->assertDatabaseCount('articles', 0);
        $this->assertEquals(1, $this->mockConnection->getTopHeadlineCallCount());
        $this->assertEquals(0, $this->mockConnection->getCallCount());
    }

    public function test_fetch_and_store_articles_handles_failed_article_request(): void
    {
        $this->mockConnection->setTopHeadlineMockResponse(200, null, [
            'sources' => [
                ['id' => 'bbc-news', 'name' => 'BBC News'],
            ]
        ]);

        $this->mockConnection->setMockResponse(500, 'Server Error', []);

        $this->provider->fetchAndStoreArticles();

        $this->assertDatabaseCount('articles', 0);
        $this->assertEquals(1, $this->mockConnection->getTopHeadlineCallCount());
        $this->assertEquals(1, $this->mockConnection->getCallCount());
    }

    public function test_fetch_and_store_articles_handles_empty_sources(): void
    {
        $this->mockConnection->setTopHeadlineMockResponse(200, null, [
            'sources' => []
        ]);

        $this->provider->fetchAndStoreArticles();

        $this->assertDatabaseCount('articles', 0);
        $this->assertEquals(1, $this->mockConnection->getTopHeadlineCallCount());
        $this->assertEquals(0, $this->mockConnection->getCallCount());
    }

    public function test_fetch_and_store_articles_handles_empty_articles(): void
    {
        $this->mockConnection->setTopHeadlineMockResponse(200, null, [
            'sources' => [
                ['id' => 'bbc-news', 'name' => 'BBC News'],
            ]
        ]);

        $this->mockConnection->setMockResponse(200, null, [
            'articles' => []
        ]);

        $this->provider->fetchAndStoreArticles();

        $this->assertDatabaseCount('articles', 0);
        $this->assertDatabaseCount('sources', 0);
    }

    public function test_fetch_and_store_articles_updates_existing_article(): void
    {
        Article::create([
            'provider' => 'news_api_dot_org',
            'provider_id' => 'test-article-1',
            'title' => 'Old Title',
            'slug' => 'old-title',
            'content' => 'Old Content',
            'link' => 'http://example.com/old',
            'published_at' => now()->subDay(),
        ]);

        $this->mockConnection->setTopHeadlineMockResponse(200, null, [
            'sources' => [
                ['id' => 'bbc-news', 'name' => 'BBC News'],
            ]
        ]);

        $this->mockConnection->setMockResponse(200, null, [
            'articles' => [
                [
                    'title' => 'Test Article 1',
                    'content' => 'Content 1',
                    'url' => 'http://example.com/1',
                    'publishedAt' => '2024-01-01T12:00:00Z',
                    'urlToImage' => 'http://example.com/image1.jpg',
                    'description' => 'Description 1',
                    'author' => 'Author 1',
                    'source' => ['id' => 'bbc-news', 'name' => 'BBC News'],
                ]
            ]
        ]);

        $this->provider->fetchAndStoreArticles();

        $this->assertDatabaseCount('articles', 1);
        $this->assertDatabaseHas('articles', [
            'title' => 'Test Article 1',
            'provider' => 'news_api_dot_org',
        ]);
    }

    public function test_fetch_and_store_articles_handles_article_without_source_id(): void
    {
        $this->mockConnection->setTopHeadlineMockResponse(200, null, [
            'sources' => [
                ['id' => 'test-source', 'name' => 'Test Source'],
            ]
        ]);

        $this->mockConnection->setMockResponse(200, null, [
            'articles' => [
                [
                    'title' => 'Test Article',
                    'content' => 'Content',
                    'url' => 'http://example.com/article',
                    'publishedAt' => '2024-01-01T12:00:00Z',
                    'urlToImage' => null,
                    'description' => 'Description',
                    'author' => null,
                    'source' => ['name' => 'Unknown Source'],
                ]
            ]
        ]);

        $this->provider->fetchAndStoreArticles();

        $this->assertDatabaseCount('articles', 1);
        $this->assertDatabaseHas('sources', [
            'key' => 'unknown-source',
            'label' => 'Unknown Source',
        ]);
    }
}
