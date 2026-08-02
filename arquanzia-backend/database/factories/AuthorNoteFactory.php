<?php

namespace Database\Factories;

use App\Models\AuthorNote;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuthorNote>
 */
class AuthorNoteFactory extends Factory
{
    protected $model = AuthorNote::class;

    public function definition(): array
    {
        return [
            'paragraph_id' => 'p-'.substr(sha1($this->faker->unique()->sentence()), 0, 8),
            'note_md' => $this->faker->sentence(12),
        ];
    }
}
