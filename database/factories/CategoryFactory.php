<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $slug = fake()->unique()->slug(2);
        $name = Str::title(str_replace('-', ' ', $slug));

        return [
            'parent_id' => null,
            'slug' => $slug,
            'name' => ['en' => $name, 'es' => $name],
            'description' => null,
            'position' => 0,
        ];
    }

    /**
     * Nest the category under the given top-level category.
     */
    public function childOf(Category $parent): static
    {
        return $this->state(fn (): array => ['parent_id' => $parent->id]);
    }
}
