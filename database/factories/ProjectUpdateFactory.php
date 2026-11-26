<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\ProjectUpdate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProjectUpdate>
 */
class ProjectUpdateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory()->revived()->published(),
            'user_id' => fn (array $attributes): int => Project::query()->whereKey($attributes['project_id'])->firstOrFail()->user_id,
            'content' => fake()->paragraphs(2, true),
        ];
    }
}
