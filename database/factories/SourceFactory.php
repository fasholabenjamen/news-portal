<?php

namespace Database\Factories;

use App\Models\Source;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Source>
 */
class SourceFactory extends Factory
{
    protected $model = Source::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->company();
        
        return [
            'key' => Str::slug($name),
            'label' => $name . ' News',
        ];
    }

    /**
     * Indicate that the source is a specific one.
     */
    public function withKey(string $key, string $label): static
    {
        return $this->state(fn (array $attributes) => [
            'key' => $key,
            'label' => $label,
        ]);
    }
}
