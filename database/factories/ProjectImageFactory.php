<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\ProjectImage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProjectImage>
 */
class ProjectImageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'disk' => 'public',
            'image_path' => 'projects/'.fake()->uuid().'.jpg',
            'caption' => fake()->sentence(),
            'sort_order' => 0,
        ];
    }
}
