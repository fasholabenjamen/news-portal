<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\Source;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Article>
 */
class ArticleFactory extends Factory
{
    protected $model = Article::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(8);
        
        return [
            'slug' => Str::slug($title),
            'provider_id' => fake()->uuid(),
            'provider' => fake()->randomElement(['news_api_dot_org', 'news_api_dot_ai', 'news_data', 'new_york_times']),
            'title' => $title,
            'description' => fake()->paragraph(3),
            'content' => fake()->paragraphs(5, true),
            'image_url' => fake()->imageUrl(800, 600, 'news'),
            'source_id' => Source::factory(),
            'author' => fake()->name(),
            'link' => fake()->url(),
            'published_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'category' => fake()->randomElement(['Technology', 'Business', 'Science', 'Health', 'Sports', 'Entertainment']),
            'language' => 'en',
            'keywords' => implode(',', fake()->words(5)),
        ];
    }

    /**
     * Indicate that the article is from a specific source.
     */
    public function forSource(Source $source): static
    {
        return $this->state(fn (array $attributes) => [
            'source_id' => $source->id,
        ]);
    }

    /**
     * Indicate that the article is from a specific provider.
     */
    public function fromProvider(string $provider): static
    {
        return $this->state(fn (array $attributes) => [
            'provider' => $provider,
        ]);
    }

    /**
     * Indicate that the article is in a specific category.
     */
    public function inCategory(string $category): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => $category,
        ]);
    }

    /**
     * Indicate that the article was published recently.
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'published_at' => fake()->dateTimeBetween('-7 days', 'now'),
        ]);
    }

    /**
     * Indicate that the article was published a while ago.
     */
    public function old(): static
    {
        return $this->state(fn (array $attributes) => [
            'published_at' => fake()->dateTimeBetween('-1 year', '-1 month'),
        ]);
    }
}
