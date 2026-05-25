<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $user = User::factory()->create([
            'name' => 'Alona Costa',
            'email' => 'alona.costa2@gmail.com',
        ]);

        Project::factory()
            ->count(6)
            ->for($user, 'owner')
            ->create()
            ->each(function (Project $project) use ($user) {
                Task::factory()
                    ->count(fake()->numberBetween(2, 5))
                    ->for($project)
                    ->for($user, 'owner')
                    ->create();
            });
    }
}
