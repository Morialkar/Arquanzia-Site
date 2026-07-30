<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        return [
            'author_user_id' => User::factory(),
            'title' => $this->faker->sentence(4),
            'preview_text' => $this->faker->sentence(10),
            'content_full' => $this->faker->paragraph(),
            'is_pinned' => false,
            'pinned_section' => null,
            'is_announcement' => false,
        ];
    }

    public function pinnedToFeed(): static
    {
        return $this->state(['is_pinned' => true, 'pinned_section' => 'feed']);
    }
}
