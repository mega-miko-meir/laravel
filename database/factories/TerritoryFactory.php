<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Territory>
 */
class TerritoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'territory' => $this->faker->word(),
            'territory_name' => $this->faker->unique()->word(),
            'department' => $this->faker->randomElement(['Sales', 'Marketing', 'Support']),
            'team' => $this->faker->word(),
            'role' => $this->faker->randomElement(['FFM', 'RM', 'Rep']),
            'manager_id' => $this->faker->word(),
            'city' => $this->faker->city(),
            'old_employee_id' => $this->faker->word(),
            'employee_id' => null,
            'parent_territory_id' => null,
        ];
    }
}
