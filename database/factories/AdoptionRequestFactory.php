<?php

namespace Database\Factories;

use App\Enums\AdoptionRequestStatus;
use App\Models\AdoptionRequest;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AdoptionRequest>
 */
class AdoptionRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory()->availableForAdoption(),
            'user_id' => User::factory(),
            'revived_project_id' => null,
            'message' => fake()->paragraph(),
            'status' => AdoptionRequestStatus::Pending,
            'responded_at' => null,
        ];
    }
}
