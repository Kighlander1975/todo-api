<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Todo>
 */
class TodoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => 1, // Zum Testen mit einem bestehenden User
            'titel' => fake()->sentence(),
            'erledigt' => fake()->boolean(),
            'faellig_am' => fake()->date()
        ];
    }
}
