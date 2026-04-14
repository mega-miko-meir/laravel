<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Employee>
 */
class EmployeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'full_name' => $this->faker->name(),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'birth_date' => $this->faker->date(),
            'email' => $this->faker->unique()->safeEmail(),
            'hiring_date' => $this->faker->date(),
            'firing_date' => null,
            'position' => $this->faker->randomElement(['FFM', 'RM', 'Rep']),
            'status' => 'active',
        ];
    }
}
