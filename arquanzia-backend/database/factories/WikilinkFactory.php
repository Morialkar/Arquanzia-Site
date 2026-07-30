<?php

namespace Database\Factories;

use App\Models\Wikilink;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Wikilink>
 */
class WikilinkFactory extends Factory
{
    protected $model = Wikilink::class;

    public function definition(): array
    {
        return [
            'term' => $this->faker->unique()->word(),
            'encyclopedia_node_id' => null,
            'custom_url' => 'https://example.test/' . $this->faker->unique()->slug(),
        ];
    }
}
