<?php

namespace Database\Factories;

use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'owner_id' => User::factory(),
            'name' => fake()->words(3, true),
            'description' => fake()->optional()->paragraph(),
            'status' => fake()->randomElement(ProjectStatus::values()),
            'due_date' => fake()->optional()->dateTimeBetween('now', '+3 months')?->format('Y-m-d'),
        ];
    }
}
