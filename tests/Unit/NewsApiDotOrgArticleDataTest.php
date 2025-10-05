<?php

namespace Tests\Unit;

use App\Contracts\Article\ArticleDataContract;
use App\Contracts\Article\HasAuthorName;
use App\Contracts\Article\HasDescription;
use App\Contracts\Article\HasImageUrl;
use App\Contracts\Article\HasSource;
use App\Services\Articles\Providers\NewsApiDotOrg\ArticleData;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class NewsApiDotOrgArticleDataTest extends TestCase
{
    private array $sampleData;
    private ArticleData $articleData;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sampleData = [
            'title' => 'Test Article Title',
            'content' => 'Test article content',
            'url' => 'https://example.com/article',
            'publishedAt' => '2024-01-01T12:00:00Z',
            'urlToImage' => 'https://example.com/image.jpg',
            'description' => 'Test description',
            'author' => 'John Doe',
            'source' => [
                'id' => 'bbc-news',
                'name' => 'BBC News'
            ]
        ];

        $this->articleData = new ArticleData($this->sampleData);
    }

    public function test_implements_article_data_contract(): void
    {
        $this->assertInstanceOf(ArticleDataContract::class, $this->articleData);
    }

    public function test_implements_has_image_url_contract(): void
    {
        $this->assertInstanceOf(HasImageUrl::class, $this->articleData);
    }

    public function test_implements_has_description_contract(): void
    {
        $this->assertInstanceOf(HasDescription::class, $this->articleData);
    }

    public function test_implements_has_author_name_contract(): void
    {
        $this->assertInstanceOf(HasAuthorName::class, $this->articleData);
    }

    public function test_implements_has_source_contract(): void
    {
        $this->assertInstanceOf(HasSource::class, $this->articleData);
    }

    public function test_get_provider_key_returns_correct_value(): void
    {
        $this->assertEquals('news_api_dot_org', $this->articleData->getProviderKey());
    }

    public function test_get_title_returns_correct_value(): void
    {
        $this->assertEquals('Test Article Title', $this->articleData->getTitle());
    }

    public function test_get_content_returns_correct_value(): void
    {
        $this->assertEquals('Test article content', $this->articleData->getContent());
    }

    public function test_get_link_returns_correct_value(): void
    {
        $this->assertEquals('https://example.com/article', $this->articleData->getLink());
    }

    public function test_get_published_at_returns_carbon_instance(): void
    {
        $publishedAt = $this->articleData->getPublishedAt();
        
        $this->assertInstanceOf(Carbon::class, $publishedAt);
        $this->assertEquals('2024-01-01 12:00:00', $publishedAt->format('Y-m-d H:i:s'));
    }

    public function test_get_image_url_returns_correct_value(): void
    {
        $this->assertEquals('https://example.com/image.jpg', $this->articleData->getImageUrl());
    }

    public function test_get_description_returns_correct_value(): void
    {
        $this->assertEquals('Test description', $this->articleData->getDesciption());
    }

    public function test_get_author_name_returns_correct_value(): void
    {
        $this->assertEquals('John Doe', $this->articleData->getAuthorName());
    }

    public function test_get_source_name_returns_correct_value(): void
    {
        $this->assertEquals('BBC News', $this->articleData->getSourceName());
    }

    public function test_get_source_key_returns_correct_value_with_id(): void
    {
        $this->assertEquals('bbc-news', $this->articleData->getSourceKey());
    }

    public function test_get_source_key_generates_slug_when_id_missing(): void
    {
        $data = $this->sampleData;
        $data['source'] = ['name' => 'Test Source Name'];
        
        $articleData = new ArticleData($data);
        
        $this->assertEquals('test-source-name', $articleData->getSourceKey());
    }

    public function test_get_source_name_returns_unknown_when_missing(): void
    {
        $data = $this->sampleData;
        $data['source'] = [];
        
        $articleData = new ArticleData($data);
        
        $this->assertEquals('Unknown', $articleData->getSourceName());
    }

    public function test_handles_null_values_gracefully(): void
    {
        $data = [
            'title' => 'Test Title',
            'content' => 'Test Content',
            'url' => 'https://example.com',
            'publishedAt' => '2024-01-01T12:00:00Z',
            'urlToImage' => null,
            'description' => null,
            'author' => null,
            'source' => ['id' => 'test', 'name' => 'Test']
        ];

        $articleData = new ArticleData($data);

        $this->assertNull($articleData->getImageUrl());
        $this->assertNull($articleData->getDesciption());
        $this->assertNull($articleData->getAuthorName());
    }
}
