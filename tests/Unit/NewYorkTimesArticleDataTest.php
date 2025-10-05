<?php

namespace Tests\Unit;

use App\Contracts\Article\ArticleDataContract;
use App\Contracts\Article\HasCategory;
use App\Contracts\Article\HasDescription;
use App\Contracts\Article\HasImageUrl;
use App\Contracts\Article\HasKeywords;
use App\Contracts\Article\HasProviderIdentity;
use App\Contracts\Article\HasSource;
use App\Services\Articles\Providers\NewYorkTimes\ArticleData;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class NewYorkTimesArticleDataTest extends TestCase
{
    private array $sampleData;
    private ArticleData $articleData;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sampleData = [
            'asset_id' => 123456789,
            'title' => 'Test NYT Article Title',
            'abstract' => 'Test article abstract',
            'url' => 'https://nytimes.com/article',
            'published_date' => '2024-01-01',
            'media' => [
                [
                    'type' => 'image',
                    'subtype' => 'photo',
                    'caption' => 'Test image caption',
                    'media-metadata' => [
                        [
                            'url' => 'https://nytimes.com/image.jpg',
                            'format' => 'Standard Thumbnail',
                            'height' => 75,
                            'width' => 75
                        ],
                        [
                            'url' => 'https://nytimes.com/image_medium.jpg',
                            'format' => 'mediumThreeByTwo210',
                            'height' => 140,
                            'width' => 210
                        ],
                        [
                            'url' => 'https://nytimes.com/image_large.jpg',
                            'format' => 'mediumThreeByTwo440',
                            'height' => 293,
                            'width' => 440
                        ]
                    ]
                ]
            ],
            'section' => 'Technology',
            'source' => 'The New York Times',
            'des_facet' => ['AI', 'Technology', 'Innovation']
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

    public function test_implements_has_keywords_contract(): void
    {
        $this->assertInstanceOf(HasKeywords::class, $this->articleData);
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

    public function test_get_provider_key_returns_correct_value(): void
    {
        $this->assertEquals('new_york_times', $this->articleData->getProviderKey());
    }

    public function test_get_title_returns_correct_value(): void
    {
        $this->assertEquals('Test NYT Article Title', $this->articleData->getTitle());
    }

    public function test_get_content_returns_abstract(): void
    {
        $this->assertEquals('Test article abstract', $this->articleData->getContent());
    }

    public function test_get_link_returns_correct_value(): void
    {
        $this->assertEquals('https://nytimes.com/article', $this->articleData->getLink());
    }

    public function test_get_published_at_returns_carbon_instance(): void
    {
        $publishedAt = $this->articleData->getPublishedAt();
        
        $this->assertInstanceOf(Carbon::class, $publishedAt);
        $this->assertEquals('2024-01-01', $publishedAt->format('Y-m-d'));
    }

    public function test_get_image_url_returns_correct_value(): void
    {
        $this->assertEquals('https://nytimes.com/image.jpg', $this->articleData->getImageUrl());
    }

    public function test_get_category_returns_correct_value(): void
    {
        $this->assertEquals('Technology', $this->articleData->getCategory());
    }

    public function test_get_description_returns_correct_value(): void
    {
        $this->assertEquals('Test article abstract', $this->articleData->getDesciption());
    }

    public function test_get_provider_id_returns_correct_value(): void
    {
        $this->assertEquals('123456789', $this->articleData->getProviderID());
    }

    public function test_get_source_key_returns_slugified_source(): void
    {
        $this->assertEquals('the-new-york-times', $this->articleData->getSourceKey());
    }

    public function test_get_source_name_returns_correct_value(): void
    {
        $this->assertEquals('The New York Times', $this->articleData->getSourceName());
    }

    public function test_get_keywords_returns_comma_separated_string(): void
    {
        $this->assertEquals('AI,Technology,Innovation', $this->articleData->getKeywords());
    }

    public function test_get_keywords_returns_empty_string_when_no_facets(): void
    {
        $data = $this->sampleData;
        $data['des_facet'] = [];
        
        $articleData = new ArticleData($data);
        
        $this->assertEquals('', $articleData->getKeywords());
    }

    public function test_get_keywords_returns_empty_string_when_facets_missing(): void
    {
        $data = $this->sampleData;
        unset($data['des_facet']);
        
        $articleData = new ArticleData($data);
        
        $this->assertEquals('', $articleData->getKeywords());
    }

    public function test_handles_null_values_gracefully(): void
    {
        $data = [
            'asset_id' => 123,
            'title' => 'Test Title',
            'abstract' => 'Test Abstract',
            'url' => 'https://example.com',
            'published_date' => '2024-01-01',
            'image_url' => null,
            'section' => null,
            'source' => 'Test Source',
            'des_facet' => null
        ];

        $articleData = new ArticleData($data);

        $this->assertNull($articleData->getImageUrl());
        $this->assertNull($articleData->getCategory());
        $this->assertEquals('', $articleData->getKeywords());
    }
}
