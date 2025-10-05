<?php

namespace Tests\Feature;

use App\Helpers\ClientResponse;
use App\Models\Article;
use App\Services\Articles\Providers\NewYorkTimes\NewYorkTimesProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\MockClientConnection;
use Tests\TestCase;

class NewYorkTimesProviderTest extends TestCase
{
    use RefreshDatabase;

    private MockClientConnection $mockConnection;
    private NewYorkTimesProvider $provider;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->mockConnection = new MockClientConnection();
        $this->provider = new NewYorkTimesProvider($this->mockConnection);
    }

    public function test_fetch_and_store_articles_successfully(): void
    {
        $this->mockConnection->setMockResponse(200, null, [
            'results' => [
                [
                    'asset_id' => 123456789,
                    'title' => 'Test NYT Article 1',
                    'abstract' => 'Abstract content 1',
                    'url' => 'https://nytimes.com/article1',
                    'published_date' => '2024-01-01',
                    'image_url' => 'https://nytimes.com/image1.jpg',
                    'section' => 'Technology',
                    'description' => 'Description 1',
                    'source' => 'The New York Times',
                    'des_facet' => ['AI', 'Technology', 'Innovation']
                ],
                [
                    'asset_id' => 987654321,
                    'title' => 'Test NYT Article 2',
                    'abstract' => 'Abstract content 2',
                    'url' => 'https://nytimes.com/article2',
                    'published_date' => '2024-01-02',
                    'image_url' => 'https://nytimes.com/image2.jpg',
                    'section' => 'Science',
                    'description' => 'Description 2',
                    'source' => 'The New York Times',
                    'des_facet' => ['Science', 'Research']
                ]
            ]
        ]);

        $this->provider->fetchAndStoreArticles();

        $this->assertDatabaseCount('articles', 2);
        
        $this->assertDatabaseHas('articles', [
            'title' => 'Test NYT Article 1',
            'provider' => 'new_york_times',
            'provider_id' => '123456789',
            'category' => 'Technology',
        ]);

        $this->assertDatabaseHas('articles', [
            'title' => 'Test NYT Article 2',
            'provider' => 'new_york_times',
            'provider_id' => '987654321',
            'category' => 'Science',
        ]);
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
            'results' => []
        ]);

        $this->provider->fetchAndStoreArticles();

        $this->assertDatabaseCount('articles', 0);
    }

    public function test_fetch_and_store_articles_handles_missing_results_key(): void
    {
        $this->mockConnection->setMockResponse(200, null, []);

        $this->provider->fetchAndStoreArticles();

        $this->assertDatabaseCount('articles', 0);
    }

    public function test_fetch_and_store_articles_with_keywords(): void
    {
        $this->mockConnection->setMockResponse(200, null, [
            'results' => [
                [
                    'asset_id' => 123456789,
                    'title' => 'Test Article with Keywords',
                    'abstract' => 'Abstract content',
                    'url' => 'https://nytimes.com/article',
                    'published_date' => '2024-01-01',
                    'image_url' => null,
                    'section' => 'Technology',
                    'description' => 'Description',
                    'source' => 'The New York Times',
                    'des_facet' => ['Keyword1', 'Keyword2', 'Keyword3']
                ]
            ]
        ]);

        $this->provider->fetchAndStoreArticles();

        $this->assertDatabaseHas('articles', [
            'title' => 'Test Article with Keywords',
            'keywords' => 'Keyword1,Keyword2,Keyword3',
        ]);
    }

    public function test_fetch_and_store_articles_with_empty_keywords(): void
    {
        $this->mockConnection->setMockResponse(200, null, [
            'results' => [
                [
                    'asset_id' => 123456789,
                    'title' => 'Test Article without Keywords',
                    'abstract' => 'Abstract content',
                    'url' => 'https://nytimes.com/article',
                    'published_date' => '2024-01-01',
                    'image_url' => null,
                    'section' => 'Technology',
                    'description' => 'Description',
                    'source' => 'The New York Times',
                    'des_facet' => []
                ]
            ]
        ]);

        $this->provider->fetchAndStoreArticles();

        $this->assertDatabaseHas('articles', [
            'title' => 'Test Article without Keywords',
            'keywords' => '',
        ]);
    }

    public function test_fetch_and_store_articles_creates_source(): void
    {
        $this->mockConnection->setMockResponse(200, null, [
            'results' => [
                [
                    'asset_id' => 123456789,
                    'title' => 'Test Article',
                    'abstract' => 'Abstract',
                    'url' => 'https://nytimes.com/article',
                    'published_date' => '2024-01-01',
                    'image_url' => null,
                    'section' => 'Technology',
                    'description' => 'Description',
                    'source' => 'The New York Times',
                    'des_facet' => []
                ]
            ]
        ]);

        $this->provider->fetchAndStoreArticles();

        $this->assertDatabaseHas('sources', [
            'key' => 'the-new-york-times',
            'label' => 'The New York Times',
        ]);
    }

    public function test_fetch_and_store_articles_updates_existing_article(): void
    {
        Article::create([
            'provider' => 'new_york_times',
            'provider_id' => '123456789',
            'title' => 'Old Title',
            'slug' => 'old-title',
            'content' => 'Old Content',
            'link' => 'https://nytimes.com/old',
            'published_at' => now()->subDay(),
        ]);

        $this->mockConnection->setMockResponse(200, null, [
            'results' => [
                [
                    'asset_id' => 123456789,
                    'title' => 'Updated Title',
                    'abstract' => 'Updated abstract',
                    'url' => 'https://nytimes.com/updated',
                    'published_date' => '2024-01-01',
                    'image_url' => null,
                    'section' => 'Technology',
                    'description' => 'Description',
                    'source' => 'The New York Times',
                    'des_facet' => []
                ]
            ]
        ]);

        $this->provider->fetchAndStoreArticles();

        $this->assertDatabaseCount('articles', 1);
        $this->assertDatabaseHas('articles', [
            'provider_id' => '123456789',
            'title' => 'Updated Title',
        ]);
    }

    public function test_fetch_and_store_articles_with_null_image_url(): void
    {
        $this->mockConnection->setMockResponse(200, null, [
            'results' => [
                [
                    'asset_id' => 123456789,
                    'title' => 'Test Article',
                    'abstract' => 'Abstract',
                    'url' => 'https://nytimes.com/article',
                    'published_date' => '2024-01-01',
                    'image_url' => null,
                    'section' => 'Technology',
                    'description' => 'Description',
                    'source' => 'The New York Times',
                    'des_facet' => []
                ]
            ]
        ]);

        $this->provider->fetchAndStoreArticles();

        $this->assertDatabaseHas('articles', [
            'title' => 'Test Article',
            'image_url' => null,
        ]);
    }
}
