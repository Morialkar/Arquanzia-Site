<?php

namespace Database\Factories;

use App\Models\PostMedia;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PostMedia>
 */
class PostMediaFactory extends Factory
{
    protected $model = PostMedia::class;

    public function definition(): array
    {
        return [
            'post_id' => null,
            'position' => 0,
            'filename' => $this->faker->unique()->slug().'.jpg',
            'mime' => 'image/jpeg',
        ];
    }
}
