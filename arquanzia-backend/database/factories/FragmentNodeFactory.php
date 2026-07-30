<?php

namespace Database\Factories;

use App\Models\FragmentNode;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<FragmentNode>
 */
class FragmentNodeFactory extends Factory
{
    protected $model = FragmentNode::class;

    public function definition(): array
    {
        $title = $this->faker->unique()->sentence(2);

        return [
            'parent_id' => null,
            'type' => 'item',
            'slug' => Str::slug($title) . '-' . $this->faker->unique()->numberBetween(1, 99999),
            'title' => $title,
            'description_md' => $this->faker->sentence(),
            'order_index' => 0,
            'is_published' => true,
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
