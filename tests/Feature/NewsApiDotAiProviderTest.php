<?php

namespace Tests\Feature;

use App\Helpers\ClientResponse;
use App\Models\Article;
use App\Services\Articles\Providers\NewsApiDotAi\NewsApiDotAiProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\MockClientConnection;
use Tests\TestCase;

class NewsApiDotAiProviderTest extends TestCase
{
    use RefreshDatabase;

    private MockClientConnection $mockConnection;
    private NewsApiDotAiProvider $provider;

    protected function setUp(): void
    {
        parent::setUp();
        
        config(['services.news_api_dot_ai.max_page' => 3]);
        
        $this->mockConnection = new MockClientConnection();
        $this->provider = new NewsApiDotAiProvider($this->mockConnection);
    }

    public function test_fetch_and_store_articles_successfully(): void
    {
        $this->mockConnection->setMockResponses([
            new ClientResponse(200, null, [
                'articles' => [
                    'results' => [
                        [
                            'uri' => 'article-uri-1',
                            'title' => 'Test Article 1',
                            'body' => 'Body content 1',
                            'url' => 'http://example.com/1',
                            'dateTimePub' => '2024-01-01T12:00:00Z',
                            'image' => 'http://example.com/image1.jpg',
                            'lang' => 'eng',
                            'source' => [
                                'uri' => 'source-1',
                                'title' => 'Source 1'
                            ],
                            'authors' => [
                                ['name' => 'Author 1']
                            ]
                        ]
                    ],
                    'pages' => 2
                ]
            ]),
            new ClientResponse(200, null, [
                'articles' => [
                    'results' => [
                        [
                            'uri' => 'article-uri-2',
                            'title' => 'Test Article 2',
                            'body' => 'Body content 2',
                            'url' => 'http://example.com/2',
                            'dateTimePub' => '2024-01-02T12:00:00Z',
                            'image' => 'http://example.com/image2.jpg',
                            'lang' => 'eng',
                            'source' => [
                                'uri' => 'source-2',
                                'title' => 'Source 2'
                            ],
                            'authors' => []
                        ]
                    ],
                    'pages' => 2
                ]
            ])
        ]);

        $this->provider->fetchAndStoreArticles();

        $this->assertDatabaseCount('articles', 2);
        
        $this->assertDatabaseHas('articles', [
            'title' => 'Test Article 1',
            'provider' => 'news_api_dot_ai',
            'provider_id' => 'article-uri-1',
        ]);

        $this->assertDatabaseHas('articles', [
            'title' => 'Test Article 2',
            'provider' => 'news_api_dot_ai',
            'provider_id' => 'article-uri-2',
        ]);

        $this->assertEquals(2, $this->mockConnection->getCallCount());
    }

    public function test_fetch_and_store_articles_respects_max_pages_config(): void
    {
        config(['services.news_api_dot_ai.max_page' => 2]);

        $this->mockConnection->setMockResponse(200, null, [
            'articles' => [
                'results' => [
                    [
                        'uri' => 'article-uri-1',
                        'title' => 'Test Article',
                        'body' => 'Body content',
                        'url' => 'http://example.com/1',
                        'dateTimePub' => '2024-01-01T12:00:00Z',
                        'image' => 'http://example.com/image.jpg',
                        'lang' => 'eng',
                        'source' => [
                            'uri' => 'source-1',
                            'title' => 'Source 1'
                        ],
                        'authors' => []
                    ]
                ],
                'pages' => 10
            ]
        ]);

        $this->provider->fetchAndStoreArticles();

        $this->assertEquals(1, $this->mockConnection->getCallCount());
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
            'articles' => [
                'results' => [],
                'pages' => 1
            ]
        ]);

        $this->provider->fetchAndStoreArticles();

        $this->assertDatabaseCount('articles', 0);
    }

    public function test_fetch_and_store_articles_stops_on_last_page(): void
    {
        $this->mockConnection->setMockResponses([
            new ClientResponse(200, null, [
                'articles' => [
                    'results' => [
                        [
                            'uri' => 'article-uri-1',
                            'title' => 'Test Article 1',
                            'body' => 'Body content',
                            'url' => 'http://example.com/1',
                            'dateTimePub' => '2024-01-01T12:00:00Z',
                            'image' => null,
                            'lang' => 'eng',
                            'source' => [
                                'uri' => 'source-1',
                                'title' => 'Source 1'
                            ],
                            'authors' => []
                        ]
                    ],
                    'pages' => 1
                ]
            ])
        ]);

        $this->provider->fetchAndStoreArticles();

        $this->assertDatabaseCount('articles', 1);
        $this->assertEquals(1, $this->mockConnection->getCallCount());
    }

    public function test_fetch_and_store_articles_handles_article_without_author(): void
    {
        $this->mockConnection->setMockResponse(200, null, [
            'articles' => [
                'results' => [
                    [
                        'uri' => 'article-uri-1',
                        'title' => 'Test Article',
                        'body' => 'Body content',
                        'url' => 'http://example.com/1',
                        'dateTimePub' => '2024-01-01T12:00:00Z',
                        'image' => null,
                        'lang' => 'eng',
                        'source' => [
                            'uri' => 'source-1',
                            'title' => 'Source 1'
                        ],
                        'authors' => []
                    ]
                ],
                'pages' => 1
            ]
        ]);

        $this->provider->fetchAndStoreArticles();

        $this->assertDatabaseHas('articles', [
            'title' => 'Test Article',
            'author' => null,
        ]);
    }

    public function test_fetch_and_store_articles_updates_existing_article_by_provider_id(): void
    {
        Article::create([
            'provider' => 'news_api_dot_ai',
            'provider_id' => 'article-uri-1',
            'title' => 'Old Title',
            'slug' => 'old-title',
            'content' => 'Old Content',
            'link' => 'http://example.com/old',
            'published_at' => now()->subDay(),
        ]);

        $this->mockConnection->setMockResponse(200, null, [
            'articles' => [
                'results' => [
                    [
                        'uri' => 'article-uri-1',
                        'title' => 'Updated Title',
                        'body' => 'Updated content',
                        'url' => 'http://example.com/updated',
                        'dateTimePub' => '2024-01-01T12:00:00Z',
                        'image' => null,
                        'lang' => 'eng',
                        'source' => [
                            'uri' => 'source-1',
                            'title' => 'Source 1'
                        ],
                        'authors' => []
                    ]
                ],
                'pages' => 1
            ]
        ]);

        $this->provider->fetchAndStoreArticles();

        $this->assertDatabaseCount('articles', 1);
        $this->assertDatabaseHas('articles', [
            'provider_id' => 'article-uri-1',
            'title' => 'Updated Title',
        ]);
    }
}
