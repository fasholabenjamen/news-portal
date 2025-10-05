<?php

namespace Tests\Unit;

use App\Contracts\Article\ArticleDataContract;
use App\Contracts\Article\HasAuthorName;
use App\Contracts\Article\HasDescription;
use App\Contracts\Article\HasImageUrl;
use App\Contracts\Article\HasLanguage;
use App\Contracts\Article\HasProviderIdentity;
use App\Contracts\Article\HasSource;
use App\Services\Articles\Providers\NewsApiDotAi\ArticleData;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class NewsApiDotAiArticleDataTest extends TestCase
{
    private array $sampleData;
    private ArticleData $articleData;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sampleData = [
            'uri' => 'article-unique-uri-123',
            'title' => 'Test AI Article Title',
            'body' => 'Test article body content',
            'url' => 'https://example.com/ai-article',
            'dateTimePub' => '2024-01-01T12:00:00Z',
            'image' => 'https://example.com/ai-image.jpg',
            'lang' => 'eng',
            'source' => [
                'uri' => 'source-uri-1',
                'title' => 'AI Source'
            ],
            'authors' => [
                ['name' => 'Jane Smith'],
                ['name' => 'Bob Johnson']
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

    public function test_implements_has_language_contract(): void
    {
        $this->assertInstanceOf(HasLanguage::class, $this->articleData);
    }

    public function test_implements_has_description_contract(): void
    {
        $this->assertInstanceOf(HasDescription::class, $this->articleData);
    }

    public function test_implements_has_provider_identity_contract(): void
    {
        $this->assertInstanceOf(HasProviderIdentity::class, $this->articleData);
    }

    public function test_implements_has_source_contract(): void
    {
        $this->assertInstanceOf(HasSource::class, $this->articleData);
    }

    public function test_implements_has_author_name_contract(): void
    {
        $this->assertInstanceOf(HasAuthorName::class, $this->articleData);
    }

    public function test_get_provider_key_returns_correct_value(): void
    {
        $this->assertEquals('news_api_dot_ai', $this->articleData->getProviderKey());
    }

    public function test_get_title_returns_correct_value(): void
    {
        $this->assertEquals('Test AI Article Title', $this->articleData->getTitle());
    }

    public function test_get_content_returns_correct_value(): void
    {
        $this->assertEquals('Test article body content', $this->articleData->getContent());
    }

    public function test_get_link_returns_correct_value(): void
    {
        $this->assertEquals('https://example.com/ai-article', $this->articleData->getLink());
    }

    public function test_get_published_at_returns_carbon_instance(): void
    {
        $publishedAt = $this->articleData->getPublishedAt();
        
        $this->assertInstanceOf(Carbon::class, $publishedAt);
        $this->assertEquals('2024-01-01 12:00:00', $publishedAt->format('Y-m-d H:i:s'));
    }

    public function test_get_image_url_returns_correct_value(): void
    {
        $this->assertEquals('https://example.com/ai-image.jpg', $this->articleData->getImageUrl());
    }

    public function test_get_language_returns_correct_value(): void
    {
        $this->assertEquals('eng', $this->articleData->getLanguage());
    }

    public function test_get_description_returns_body_content(): void
    {
        $this->assertEquals('Test article body content', $this->articleData->getDesciption());
    }

    public function test_get_provider_id_returns_correct_value(): void
    {
        $this->assertEquals('article-unique-uri-123', $this->articleData->getProviderID());
    }

    public function test_get_source_key_returns_correct_value(): void
    {
        $this->assertEquals('source-uri-1', $this->articleData->getSourceKey());
    }

    public function test_get_source_name_returns_correct_value(): void
    {
        $this->assertEquals('AI Source', $this->articleData->getSourceName());
    }

    public function test_get_author_name_returns_first_author(): void
    {
        $this->assertEquals('Jane Smith', $this->articleData->getAuthorName());
    }

    public function test_get_author_name_returns_null_when_no_authors(): void
    {
        $data = $this->sampleData;
        $data['authors'] = [];
        
        $articleData = new ArticleData($data);
        
        $this->assertNull($articleData->getAuthorName());
    }

    public function test_handles_null_values_gracefully(): void
    {
        $data = [
            'uri' => 'article-uri',
            'title' => 'Test Title',
            'body' => 'Test Body',
            'url' => 'https://example.com',
            'dateTimePub' => '2024-01-01T12:00:00Z',
            'image' => null,
            'lang' => null,
            'source' => [
                'uri' => 'source-uri',
                'title' => 'Source'
            ],
            'authors' => []
        ];

        $articleData = new ArticleData($data);

        $this->assertNull($articleData->getImageUrl());
        $this->assertNull($articleData->getLanguage());
        $this->assertNull($articleData->getAuthorName());
    }
}
