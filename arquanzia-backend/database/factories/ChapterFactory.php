<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\Chapter;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Chapter>
 */
class ChapterFactory extends Factory
{
    protected $model = Chapter::class;

    public function definition(): array
    {
        $title = $this->faker->unique()->sentence(3);

        return [
            'book_id' => Book::factory(),
            'slug' => Str::slug($title).'-'.$this->faker->unique()->numberBetween(1, 99999),
            'title' => $title,
            'order_index' => 0,
            'content_md' => $this->faker->paragraphs(2, true),
            'is_published' => true,
            'published_at' => now()->subDay(),
        ];
    }

    public function draft(): static
    {
        return $this->state(['is_published' => false]);
    }

    /** Publié, mais avec une date de parution future : « bientôt disponible ». */
    public function comingSoon(): static
    {
        return $this->state(['is_published' => true, 'published_at' => now()->addWeek()]);
    }
}
