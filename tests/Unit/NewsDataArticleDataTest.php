<?php

namespace Tests\Unit;

use App\Contracts\Article\ArticleDataContract;
use App\Contracts\Article\HasCategory;
use App\Contracts\Article\HasDescription;
use App\Contracts\Article\HasImageUrl;
use App\Contracts\Article\HasKeywords;
use App\Contracts\Article\HasLanguage;
use App\Contracts\Article\HasProviderIdentity;
use App\Contracts\Article\HasSource;
use App\Services\Articles\Providers\NewsData\ArticleData;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class NewsDataArticleDataTest extends TestCase
{
    private array $sampleData;
    private ArticleData $articleData;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sampleData = [
            'article_id' => 'article-unique-id-123',
            'title' => 'Test NewsData Article Title',
            'content' => 'Test article content',
            'link' => 'https://example.com/newsdata-article',
            'pubDate' => '2024-01-01 12:00:00',
            'image_url' => 'https://example.com/newsdata-image.jpg',
            'language' => 'en',
            'description' => 'Test description',
            'source_id' => 'source-unique-id',
            'source_name' => 'NewsData Source',
            'category' => ['technology', 'business'],
            'keywords' => ['tech', 'innovation', 'business']
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

    public function test_implements_has_category_contract(): void
    {
        $this->assertInstanceOf(HasCategory::class, $this->articleData);
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

    public function test_implements_has_keywords_contract(): void
    {
        $this->assertInstanceOf(HasKeywords::class, $this->articleData);
    }

    public function test_get_provider_key_returns_correct_value(): void
    {
        $this->assertEquals('news_data', $this->articleData->getProviderKey());
    }

    public function test_get_title_returns_correct_value(): void
    {
        $this->assertEquals('Test NewsData Article Title', $this->articleData->getTitle());
    }

    public function test_get_content_returns_correct_value(): void
    {
        $this->assertEquals('Test article content', $this->articleData->getContent());
    }

    public function test_get_link_returns_correct_value(): void
    {
        $this->assertEquals('https://example.com/newsdata-article', $this->articleData->getLink());
    }

    public function test_get_published_at_returns_carbon_instance(): void
    {
        $publishedAt = $this->articleData->getPublishedAt();
        
        $this->assertInstanceOf(Carbon::class, $publishedAt);
        $this->assertEquals('2024-01-01 12:00:00', $publishedAt->format('Y-m-d H:i:s'));
    }

    public function test_get_image_url_returns_correct_value(): void
    {
        $this->assertEquals('https://example.com/newsdata-image.jpg', $this->articleData->getImageUrl());
    }

    public function test_get_category_returns_space_separated_string(): void
    {
        $this->assertEquals('technology business', $this->articleData->getCategory());
    }

    public function test_get_category_returns_string_when_category_is_string(): void
    {
        $data = $this->sampleData;
        $data['category'] = 'technology';
        
        $articleData = new ArticleData($data);
        
        $this->assertEquals('technology', $articleData->getCategory());
    }

    public function test_get_category_filters_top_from_array(): void
    {
        $data = $this->sampleData;
        $data['category'] = ['top', 'technology', 'business'];
        
        $articleData = new ArticleData($data);
        
        $this->assertEquals('technology business', $articleData->getCategory());
    }

    public function test_get_language_returns_correct_value(): void
    {
        $this->assertEquals('en', $this->articleData->getLanguage());
    }

    public function test_get_description_returns_correct_value(): void
    {
        $this->assertEquals('Test description', $this->articleData->getDesciption());
    }

    public function test_get_provider_id_returns_correct_value(): void
    {
        $this->assertEquals('article-unique-id-123', $this->articleData->getProviderID());
    }

    public function test_get_source_key_returns_correct_value(): void
    {
        $this->assertEquals('source-unique-id', $this->articleData->getSourceKey());
    }

    public function test_get_source_name_returns_correct_value(): void
    {
        $this->assertEquals('NewsData Source', $this->articleData->getSourceName());
    }

    public function test_get_keywords_returns_comma_separated_string(): void
    {
        $this->assertEquals('tech,innovation,business', $this->articleData->getKeywords());
    }

    public function test_get_keywords_returns_empty_string_when_no_keywords(): void
    {
        $data = $this->sampleData;
        $data['keywords'] = [];
        
        $articleData = new ArticleData($data);
        
        $this->assertEquals('', $articleData->getKeywords());
    }

    public function test_get_keywords_returns_empty_string_when_keywords_missing(): void
    {
        $data = $this->sampleData;
        unset($data['keywords']);
        
        $articleData = new ArticleData($data);
        
        $this->assertEquals('', $articleData->getKeywords());
    }

    public function test_handles_null_values_gracefully(): void
    {
        $data = [
            'article_id' => 'article-id',
            'title' => 'Test Title',
            'content' => 'Test Content',
            'link' => 'https://example.com',
            'pubDate' => '2024-01-01 12:00:00',
            'image_url' => null,
            'language' => null,
            'description' => null,
            'source_id' => 'source-id',
            'source_name' => 'Source',
            'category' => 'test',
            'keywords' => []
        ];

        $articleData = new ArticleData($data);

        $this->assertNull($articleData->getImageUrl());
        $this->assertNull($articleData->getLanguage());
        $this->assertNull($articleData->getDesciption());
        $this->assertEquals('', $articleData->getKeywords());
    }

    public function test_parse_category_handles_only_top_in_array(): void
    {
        $data = $this->sampleData;
        $data['category'] = ['top'];
        
        $articleData = new ArticleData($data);
        
        $this->assertEquals('', $articleData->getCategory());
    }
}
