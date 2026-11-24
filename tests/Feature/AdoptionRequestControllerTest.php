<?php

use App\Enums\AdoptionRequestStatus;
use App\Enums\ProjectStatus;
use App\Models\AdoptionRequest;
use App\Models\Project;
use App\Models\User;

test('an explorer can request an available published project', function () {
    $creator = User::factory()->create();
    $explorer = User::factory()->create();
    $project = Project::factory()->for($creator, 'creator')->availableForAdoption()->create();

    $response = $this->actingAs($explorer)->post(route('projects.adoption-requests.store', $project), [
        'message' => 'I would like to continue this project with a smaller first release.',
    ]);

    $response->assertRedirectToRoute('projects.show', $project);
    $this->assertDatabaseHas('adoption_requests', [
        'project_id' => $project->id,
        'user_id' => $explorer->id,
        'status' => AdoptionRequestStatus::Pending->value,
    ]);
});

test('a creator cannot request adoption of their own project', function () {
    $creator = User::factory()->create();
    $project = Project::factory()->for($creator, 'creator')->availableForAdoption()->create();

    $this->actingAs($creator)
        ->post(route('projects.adoption-requests.store', $project), [
            'message' => 'I want to adopt my own project.',
        ])
        ->assertForbidden();

    $this->assertDatabaseCount('adoption_requests', 0);
});

test('an unavailable project cannot receive an adoption request', function () {
    $creator = User::factory()->create();
    $explorer = User::factory()->create();
    $project = Project::factory()->for($creator, 'creator')->published()->create();

    $this->actingAs($explorer)
        ->post(route('projects.adoption-requests.store', $project), [
            'message' => 'I would like to continue this idea.',
        ])
        ->assertForbidden();

    $this->assertDatabaseCount('adoption_requests', 0);
});

test('an adoption request needs a message within the allowed length', function () {
    $creator = User::factory()->create();
    $explorer = User::factory()->create();
    $project = Project::factory()->for($creator, 'creator')->availableForAdoption()->create();

    $response = $this->actingAs($explorer)->post(route('projects.adoption-requests.store', $project), [
        'message' => str_repeat('a', 3001),
    ]);

    $response->assertSessionHasErrors('message');
    $this->assertDatabaseCount('adoption_requests', 0);
});

test('the project creator can approve a request and receive a linked revived draft', function () {
    $creator = User::factory()->create();
    $explorer = User::factory()->create();
    $project = Project::factory()->for($creator, 'creator')->availableForAdoption()->create();
    $request = AdoptionRequest::factory()->for($project)->for($explorer, 'requester')->create();
    $otherPendingRequest = AdoptionRequest::factory()->for($project)->create();

    $response = $this->actingAs($creator)->patch(route('my.adoption-requests.approve', $request));

    $response->assertRedirectToRoute('my.projects.edit', $project);
    $this->assertDatabaseHas('adoption_requests', [
        'id' => $request->id,
        'status' => AdoptionRequestStatus::Approved->value,
    ]);
    $this->assertDatabaseHas('adoption_requests', [
        'id' => $otherPendingRequest->id,
        'status' => AdoptionRequestStatus::Rejected->value,
    ]);
    $this->assertDatabaseHas('projects', [
        'parent_project_id' => $project->id,
        'user_id' => $explorer->id,
        'title' => $project->title,
        'status' => ProjectStatus::Draft->value,
        'adoption_available' => false,
    ]);
    $this->assertDatabaseHas('projects', [
        'id' => $project->id,
        'status' => ProjectStatus::Published->value,
        'adoption_available' => false,
    ]);
});

test('an explorer cannot approve their own adoption request', function () {
    $creator = User::factory()->create();
    $explorer = User::factory()->create();
    $project = Project::factory()->for($creator, 'creator')->availableForAdoption()->create();
    $request = AdoptionRequest::factory()->for($project)->for($explorer, 'requester')->create();

    $this->actingAs($explorer)
        ->patch(route('my.adoption-requests.approve', $request))
        ->assertForbidden();

    $this->assertDatabaseHas('adoption_requests', [
        'id' => $request->id,
        'status' => AdoptionRequestStatus::Pending->value,
    ]);
    $this->assertDatabaseMissing('projects', [
        'parent_project_id' => $project->id,
    ]);
});

test('the project creator can reject an adoption request', function () {
    $creator = User::factory()->create();
    $explorer = User::factory()->create();
    $project = Project::factory()->for($creator, 'creator')->availableForAdoption()->create();
    $request = AdoptionRequest::factory()->for($project)->for($explorer, 'requester')->create();

    $response = $this->actingAs($creator)->patch(route('my.adoption-requests.reject', $request));

    $response->assertRedirectToRoute('my.projects.edit', $project);
    $this->assertDatabaseHas('adoption_requests', [
        'id' => $request->id,
        'status' => AdoptionRequestStatus::Rejected->value,
    ]);
    $this->assertDatabaseMissing('projects', [
        'parent_project_id' => $project->id,
    ]);
});

test('an explorer can cancel their pending adoption request', function () {
    $creator = User::factory()->create();
    $explorer = User::factory()->create();
    $project = Project::factory()->for($creator, 'creator')->availableForAdoption()->create();
    $request = AdoptionRequest::factory()->for($project)->for($explorer, 'requester')->create();

    $response = $this->actingAs($explorer)->patch(route('my.adoption-requests.cancel', $request));

    $response->assertRedirectToRoute('projects.show', $project);
    $this->assertDatabaseHas('adoption_requests', [
        'id' => $request->id,
        'status' => AdoptionRequestStatus::Cancelled->value,
    ]);
});

test('an explorer cannot submit a second pending request for the same project', function () {
    $creator = User::factory()->create();
    $explorer = User::factory()->create();
    $project = Project::factory()->for($creator, 'creator')->availableForAdoption()->create();
    AdoptionRequest::factory()->for($project)->for($explorer, 'requester')->create();

    $response = $this->actingAs($explorer)->post(route('projects.adoption-requests.store', $project), [
        'message' => 'I am submitting a duplicate request.',
    ]);

    $response->assertSessionHasErrors('message');
    $this->assertDatabaseCount('adoption_requests', 1);
});
