<?php

namespace Database\Factories;

use App\Enums\ProjectStatus;
use App\Models\Category;
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
        $title = fake()->sentence(4);

        return [
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'parent_project_id' => null,
            'title' => rtrim($title, '.'),
            'description' => fake()->sentence(12),
            'story' => fake()->paragraphs(3, true),
            'abandonment_reason' => fake()->paragraph(),
            'original_goal' => fake()->paragraph(),
            'what_went_wrong' => fake()->paragraph(),
            'what_could_be_improved' => fake()->paragraph(),
            'status' => ProjectStatus::Draft,
            'adoption_available' => false,
        ];
    }

    /**
     * Set the project to published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => ProjectStatus::Published,
        ]);
    }

    /**
     * Make the published project available for adoption.
     */
    public function availableForAdoption(): static
    {
        return $this->published()->state(fn (array $attributes): array => [
            'adoption_available' => true,
        ]);
    }

    /**
     * Make this a new project revived from another project.
     */
    public function revived(): static
    {
        return $this->state(fn (array $attributes): array => [
            'parent_project_id' => Project::factory()->published(),
        ]);
    }
}
