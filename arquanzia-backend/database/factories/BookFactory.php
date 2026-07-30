<?php

namespace Database\Factories;

use App\Models\Book;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Book>
 */
class BookFactory extends Factory
{
    protected $model = Book::class;

    public function definition(): array
    {
        $title = $this->faker->unique()->sentence(3);

        return [
            'slug' => Str::slug($title).'-'.$this->faker->unique()->numberBetween(1, 99999),
            'title' => $title,
            'author' => $this->faker->name(),
            'description_md' => $this->faker->paragraph(),
            'is_published' => true,
            'slug_locked_at' => now(),
        ];
    }

    public function draft(): static
    {
        return $this->state(['is_published' => false, 'slug_locked_at' => null]);
    }
}
