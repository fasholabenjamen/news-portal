<?php

namespace Tests\Unit;

use App\Enums\ArticleProviders;
use App\Services\Articles\Providers\BaseProvider;
use App\Services\Articles\Providers\NewsApiDotAi\NewsApiDotAiProvider;
use App\Services\Articles\Providers\NewsApiDotOrg\NewsApiDotOrgProvider;
use App\Services\Articles\Providers\NewsData\NewsDataProvider;
use App\Services\Articles\Providers\NewYorkTimes\NewYorkTimesProvider;
use Tests\TestCase;

class ArticleProvidersEnumTest extends TestCase
{
    public function test_enum_has_all_expected_cases(): void
    {
        $cases = ArticleProviders::cases();
        
        $this->assertCount(4, $cases);
        
        $expectedCases = [
            ArticleProviders::NEWS_API_DOT_ORG,
            ArticleProviders::NEWS_API_DOT_AI,
            ArticleProviders::NEWS_DATA,
            ArticleProviders::NEW_YORK_TIMES,
        ];

        foreach ($expectedCases as $expected) {
            $this->assertContains($expected, $cases);
        }
    }

    public function test_enum_values_are_correct(): void
    {
        $this->assertEquals('news_api_dot_org', ArticleProviders::NEWS_API_DOT_ORG->value);
        $this->assertEquals('news_api_dot_ai', ArticleProviders::NEWS_API_DOT_AI->value);
        $this->assertEquals('news_data', ArticleProviders::NEWS_DATA->value);
        $this->assertEquals('new_york_times', ArticleProviders::NEW_YORK_TIMES->value);
    }

    public function test_label_method_returns_correct_labels(): void
    {
        $this->assertEquals(
            'News API (newsapi.org)', 
            ArticleProviders::NEWS_API_DOT_ORG->label()
        );
        
        $this->assertEquals(
            'News API (newsapi.ai)', 
            ArticleProviders::NEWS_API_DOT_AI->label()
        );
        
        $this->assertEquals(
            'News Data (newsdata.io)', 
            ArticleProviders::NEWS_DATA->label()
        );
        
        $this->assertEquals(
            'New York Times (nytimes.com)', 
            ArticleProviders::NEW_YORK_TIMES->label()
        );
    }

    public function test_get_instance_returns_correct_provider_types(): void
    {
        $newsApiDotOrgInstance = ArticleProviders::NEWS_API_DOT_ORG->getInstance();
        $this->assertInstanceOf(NewsApiDotOrgProvider::class, $newsApiDotOrgInstance);
        $this->assertInstanceOf(BaseProvider::class, $newsApiDotOrgInstance);

        $newsApiDotAiInstance = ArticleProviders::NEWS_API_DOT_AI->getInstance();
        $this->assertInstanceOf(NewsApiDotAiProvider::class, $newsApiDotAiInstance);
        $this->assertInstanceOf(BaseProvider::class, $newsApiDotAiInstance);

        $newsDataInstance = ArticleProviders::NEWS_DATA->getInstance();
        $this->assertInstanceOf(NewsDataProvider::class, $newsDataInstance);
        $this->assertInstanceOf(BaseProvider::class, $newsDataInstance);

        $newYorkTimesInstance = ArticleProviders::NEW_YORK_TIMES->getInstance();
        $this->assertInstanceOf(NewYorkTimesProvider::class, $newYorkTimesInstance);
        $this->assertInstanceOf(BaseProvider::class, $newYorkTimesInstance);
    }

    public function test_enum_can_be_instantiated_from_value(): void
    {
        $provider = ArticleProviders::from('news_api_dot_org');
        $this->assertEquals(ArticleProviders::NEWS_API_DOT_ORG, $provider);

        $provider = ArticleProviders::from('news_api_dot_ai');
        $this->assertEquals(ArticleProviders::NEWS_API_DOT_AI, $provider);

        $provider = ArticleProviders::from('news_data');
        $this->assertEquals(ArticleProviders::NEWS_DATA, $provider);

        $provider = ArticleProviders::from('new_york_times');
        $this->assertEquals(ArticleProviders::NEW_YORK_TIMES, $provider);
    }

    public function test_enum_try_from_returns_null_for_invalid_value(): void
    {
        $provider = ArticleProviders::tryFrom('invalid_provider');
        $this->assertNull($provider);
    }

    public function test_all_enum_cases_have_unique_values(): void
    {
        $cases = ArticleProviders::cases();
        $values = array_map(fn($case) => $case->value, $cases);
        
        $this->assertCount(count($values), array_unique($values));
    }

    public function test_all_enum_cases_have_unique_labels(): void
    {
        $cases = ArticleProviders::cases();
        $labels = array_map(fn($case) => $case->label(), $cases);
        
        $this->assertCount(count($labels), array_unique($labels));
    }

    public function test_enum_is_backed_by_string(): void
    {
        $reflection = new \ReflectionEnum(ArticleProviders::class);
        $this->assertTrue($reflection->isBacked());
        $this->assertEquals('string', $reflection->getBackingType()->getName());
    }
}
