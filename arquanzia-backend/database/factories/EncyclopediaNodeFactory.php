<?php

namespace Database\Factories;

use App\Models\EncyclopediaNode;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<EncyclopediaNode>
 */
class EncyclopediaNodeFactory extends Factory
{
    protected $model = EncyclopediaNode::class;

    public function definition(): array
    {
        $title = $this->faker->unique()->sentence(2);

        return [
            'parent_id' => null,
            'type' => 'article',
            'slug' => Str::slug($title) . '-' . $this->faker->unique()->numberBetween(1, 99999),
            'title' => $title,
            'is_published' => true,
            'teaser_md' => $this->faker->sentence(),
            'order_index' => 0,
        ];
    }

    public function category(): static
    {
        return $this->state(['type' => 'category']);
    }

    public function draft(): static
    {
        return $this->state(['is_published' => false]);
    }
}
