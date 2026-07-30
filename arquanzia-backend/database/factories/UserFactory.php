<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'handle' => $this->faker->unique()->userName(),
            'email' => $this->faker->unique()->safeEmail(),
        ];
    }
}
