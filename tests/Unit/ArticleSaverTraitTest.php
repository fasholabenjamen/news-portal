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
use App\Models\Article;
use App\Models\Source;
use App\Traits\ArticleSaver;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class ArticleSaverTraitTest extends TestCase
{
    use RefreshDatabase;

    private $trait;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->trait = new class {
            use ArticleSaver;
            
            public function testGenerateSlug(string $title): string
            {
                return $this->generateSlug($title);
            }
        };
    }

    public function test_generate_slug_creates_slug_from_title(): void
    {
        $title = 'This is a Test Title';
        $slug = $this->trait->testGenerateSlug($title);
        
        $this->assertEquals('this-is-a-test-title', $slug);
    }

    public function test_save_article_creates_new_article(): void
    {
        $articleData = $this->createMockArticleData();
        
        $this->trait->saveArticle($articleData);
        
        $this->assertDatabaseHas('articles', [
            'title' => 'Test Article Title',
            'provider' => 'test_provider',
        ]);
    }

    public function test_save_article_with_provider_identity(): void
    {
        $articleData = $this->createMockArticleDataWithProviderIdentity();
        
        $this->trait->saveArticle($articleData);
        
        $this->assertDatabaseHas('articles', [
            'provider_id' => 'provider-unique-id-123',
        ]);
    }

    public function test_save_article_updates_existing_article(): void
    {
        $articleData = $this->createMockArticleData();
        
        Article::create([
            'provider' => 'test_provider',
            'provider_id' => 'test-article-title',
            'title' => 'Old Title',
            'slug' => 'old-title',
            'content' => 'Old Content',
            'link' => 'http://example.com/old',
            'published_at' => Carbon::now()->subDay(),
        ]);
        
        $this->trait->saveArticle($articleData);
        
        $this->assertDatabaseHas('articles', [
            'title' => 'Test Article Title',
            'provider' => 'test_provider',
        ]);
        
        $this->assertDatabaseCount('articles', 1);
    }

    public function test_save_article_with_optional_fields(): void
    {
        $articleData = $this->createMockArticleDataWithAllFields();
        
        $this->trait->saveArticle($articleData);
        
        $this->assertDatabaseHas('articles', [
            'title' => 'Test Article Title',
            'description' => 'Test Description',
            'keywords' => 'test,keywords',
            'language' => 'en',
            'author' => 'John Doe',
            'category' => 'Technology',
            'image_url' => 'http://example.com/image.jpg',
        ]);
    }

    public function test_save_article_with_source(): void
    {
        $articleData = $this->createMockArticleDataWithSource();
        
        $this->trait->saveArticle($articleData);
        
        $this->assertDatabaseHas('sources', [
            'key' => 'test-source',
            'label' => 'Test Source',
        ]);
        
        $article = Article::where('title', 'Test Article Title')->first();
        $this->assertNotNull($article->source_id);
    }

    public function test_save_articles_collection(): void
    {
        $articles = new Collection([
            $this->createMockArticleData(['title' => 'Article 1']),
            $this->createMockArticleData(['title' => 'Article 2']),
            $this->createMockArticleData(['title' => 'Article 3']),
        ]);
        
        $this->trait->saveArticles($articles);
        
        $this->assertDatabaseCount('articles', 3);
    }

    private function createMockArticleData(array $override = []): ArticleDataContract
    {
        return new class($override) implements ArticleDataContract {
            public function __construct(private array $override = []) {}
            
            public function getProviderKey(): string
            {
                return $this->override['provider'] ?? 'test_provider';
            }
            
            public function getTitle(): string
            {
                return $this->override['title'] ?? 'Test Article Title';
            }
            
            public function getContent(): string
            {
                return $this->override['content'] ?? 'Test Article Content';
            }
            
            public function getLink(): string
            {
                return $this->override['link'] ?? 'http://example.com/article';
            }
            
            public function getPublishedAt(): Carbon
            {
                return $this->override['published_at'] ?? Carbon::now();
            }
        };
    }

    private function createMockArticleDataWithProviderIdentity(): ArticleDataContract&HasProviderIdentity
    {
        return new class implements ArticleDataContract, HasProviderIdentity {
            public function getProviderKey(): string
            {
                return 'test_provider';
            }
            
            public function getTitle(): string
            {
                return 'Test Article Title';
            }
            
            public function getContent(): string
            {
                return 'Test Article Content';
            }
            
            public function getLink(): string
            {
                return 'http://example.com/article';
            }
            
            public function getPublishedAt(): Carbon
            {
                return Carbon::now();
            }
            
            public function getProviderID(): string
            {
                return 'provider-unique-id-123';
            }
        };
    }

    private function createMockArticleDataWithAllFields(): ArticleDataContract
    {
        return new class implements 
            ArticleDataContract, 
            HasDescription, 
            HasKeywords, 
            HasLanguage, 
            HasAuthorName, 
            HasCategory, 
            HasImageUrl 
        {
            public function getProviderKey(): string
            {
                return 'test_provider';
            }
            
            public function getTitle(): string
            {
                return 'Test Article Title';
            }
            
            public function getContent(): string
            {
                return 'Test Article Content';
            }
            
            public function getLink(): string
            {
                return 'http://example.com/article';
            }
            
            public function getPublishedAt(): Carbon
            {
                return Carbon::now();
            }
            
            public function getDesciption(): ?string
            {
                return 'Test Description';
            }
            
            public function getKeywords(): ?string
            {
                return 'test,keywords';
            }
            
            public function getLanguage(): ?string
            {
                return 'en';
            }
            
            public function getAuthorName(): ?string
            {
                return 'John Doe';
            }
            
            public function getCategory(): ?string
            {
                return 'Technology';
            }
            
            public function getImageUrl(): ?string
            {
                return 'http://example.com/image.jpg';
            }
        };
    }

    private function createMockArticleDataWithSource(): ArticleDataContract
    {
        return new class implements ArticleDataContract, HasSource {
            public function getProviderKey(): string
            {
                return 'test_provider';
            }
            
            public function getTitle(): string
            {
                return 'Test Article Title';
            }
            
            public function getContent(): string
            {
                return 'Test Article Content';
            }
            
            public function getLink(): string
            {
                return 'http://example.com/article';
            }
            
            public function getPublishedAt(): Carbon
            {
                return Carbon::now();
            }
            
            public function getSourceName(): string
            {
                return 'Test Source';
            }
            
            public function getSourceKey(): string
            {
                return 'test-source';
            }
        };
    }
}
