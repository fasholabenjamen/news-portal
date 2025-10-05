<?php

namespace Tests\Unit;

use App\Contracts\Article\ArticleDataContract;
use App\Contracts\Article\HasAuthorName;
use App\Contracts\Article\HasCategory;
use App\Contracts\Article\HasDescription;
use App\Contracts\Article\HasImageUrl;
use App\Contracts\Article\HasKeywords;
use App\Contracts\Article\HasLanguage;
use App\Contracts\Article\HasProviderIdentity;
use App\Contracts\Article\HasSource;
use App\Contracts\Connectors\ConnectorContract;
use App\Contracts\Connectors\NewsApiDotOrgConnector;
use App\Contracts\Provider\ArticleProvider;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class ContractsTest extends TestCase
{
    public function test_article_data_contract_has_required_methods(): void
    {
        $reflection = new ReflectionClass(ArticleDataContract::class);
        
        $this->assertTrue($reflection->hasMethod('getProviderKey'));
        $this->assertTrue($reflection->hasMethod('getTitle'));
        $this->assertTrue($reflection->hasMethod('getContent'));
        $this->assertTrue($reflection->hasMethod('getLink'));
        $this->assertTrue($reflection->hasMethod('getPublishedAt'));
    }

    public function test_article_data_contract_get_published_at_returns_carbon(): void
    {
        $reflection = new ReflectionClass(ArticleDataContract::class);
        $method = $reflection->getMethod('getPublishedAt');
        $returnType = $method->getReturnType();

        $this->assertEquals(Carbon::class, $returnType->getName());
    }

    public function test_has_author_name_contract_has_required_method(): void
    {
        $reflection = new ReflectionClass(HasAuthorName::class);
        
        $this->assertTrue($reflection->hasMethod('getAuthorName'));
        $this->assertTrue($reflection->isInterface());
    }

    public function test_has_category_contract_has_required_method(): void
    {
        $reflection = new ReflectionClass(HasCategory::class);
        
        $this->assertTrue($reflection->hasMethod('getCategory'));
        $this->assertTrue($reflection->isInterface());
    }

    public function test_has_description_contract_has_required_method(): void
    {
        $reflection = new ReflectionClass(HasDescription::class);
        
        $this->assertTrue($reflection->hasMethod('getDesciption'));
        $this->assertTrue($reflection->isInterface());
    }

    public function test_has_image_url_contract_has_required_method(): void
    {
        $reflection = new ReflectionClass(HasImageUrl::class);
        
        $this->assertTrue($reflection->hasMethod('getImageUrl'));
        $this->assertTrue($reflection->isInterface());
    }

    public function test_has_keywords_contract_has_required_method(): void
    {
        $reflection = new ReflectionClass(HasKeywords::class);
        
        $this->assertTrue($reflection->hasMethod('getKeywords'));
        $this->assertTrue($reflection->isInterface());
    }

    public function test_has_language_contract_has_required_method(): void
    {
        $reflection = new ReflectionClass(HasLanguage::class);
        
        $this->assertTrue($reflection->hasMethod('getLanguage'));
        $this->assertTrue($reflection->isInterface());
    }

    public function test_has_provider_identity_contract_has_required_method(): void
    {
        $reflection = new ReflectionClass(HasProviderIdentity::class);
        
        $this->assertTrue($reflection->hasMethod('getProviderID'));
        $this->assertTrue($reflection->isInterface());
    }

    public function test_has_source_contract_has_required_methods(): void
    {
        $reflection = new ReflectionClass(HasSource::class);
        
        $this->assertTrue($reflection->hasMethod('getSourceName'));
        $this->assertTrue($reflection->hasMethod('getSourceKey'));
        $this->assertTrue($reflection->isInterface());
    }

    public function test_connector_contract_has_required_method(): void
    {
        $reflection = new ReflectionClass(ConnectorContract::class);
        
        $this->assertTrue($reflection->hasMethod('getArticles'));
        $this->assertTrue($reflection->isInterface());
    }

    public function test_news_api_dot_org_connector_extends_connector_contract(): void
    {
        $reflection = new ReflectionClass(NewsApiDotOrgConnector::class);
        
        $this->assertTrue($reflection->implementsInterface(ConnectorContract::class));
        $this->assertTrue($reflection->hasMethod('getTopHeadlineSource'));
    }

    public function test_article_provider_contract_has_required_method(): void
    {
        $reflection = new ReflectionClass(ArticleProvider::class);
        
        $this->assertTrue($reflection->hasMethod('fetchAndStoreArticles'));
        $this->assertTrue($reflection->isInterface());
    }

    public function test_article_provider_fetch_and_store_articles_returns_void(): void
    {
        $reflection = new ReflectionClass(ArticleProvider::class);
        $method = $reflection->getMethod('fetchAndStoreArticles');
        $returnType = $method->getReturnType();

        $this->assertEquals('void', $returnType->getName());
    }

    public function test_optional_contracts_return_nullable_values(): void
    {
        $contracts = [
            HasAuthorName::class => 'getAuthorName',
            HasCategory::class => 'getCategory',
            HasDescription::class => 'getDesciption',
            HasImageUrl::class => 'getImageUrl',
            HasKeywords::class => 'getKeywords',
            HasLanguage::class => 'getLanguage',
        ];

        foreach ($contracts as $contract => $method) {
            $reflection = new ReflectionClass($contract);
            $methodReflection = $reflection->getMethod($method);
            $returnType = $methodReflection->getReturnType();

            $this->assertTrue(
                $returnType->allowsNull(), 
                "{$contract}::{$method} should return nullable value"
            );
        }
    }

    public function test_required_contracts_return_non_nullable_values(): void
    {
        $contracts = [
            HasProviderIdentity::class => 'getProviderID',
            HasSource::class => ['getSourceName', 'getSourceKey'],
        ];

        foreach ($contracts as $contract => $methods) {
            $reflection = new ReflectionClass($contract);
            $methodArray = is_array($methods) ? $methods : [$methods];
            
            foreach ($methodArray as $method) {
                $methodReflection = $reflection->getMethod($method);
                $returnType = $methodReflection->getReturnType();

                $this->assertFalse(
                    $returnType->allowsNull(),
                    "{$contract}::{$method} should return non-nullable value"
                );
            }
        }
    }

    public function test_all_contracts_are_interfaces(): void
    {
        $contracts = [
            ArticleDataContract::class,
            HasAuthorName::class,
            HasCategory::class,
            HasDescription::class,
            HasImageUrl::class,
            HasKeywords::class,
            HasLanguage::class,
            HasProviderIdentity::class,
            HasSource::class,
            ConnectorContract::class,
            NewsApiDotOrgConnector::class,
            ArticleProvider::class,
        ];

        foreach ($contracts as $contract) {
            $reflection = new ReflectionClass($contract);
            $this->assertTrue($reflection->isInterface(), "{$contract} should be an interface");
        }
    }
}
