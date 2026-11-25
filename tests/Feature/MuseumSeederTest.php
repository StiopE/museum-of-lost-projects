<?php

use App\Models\AdoptionRequest;
use App\Models\Project;
use App\Models\User;

test('seeding provides an explorable collection and a reviewable request without duplicating demo work', function () {
    $this->seed();
    $request = AdoptionRequest::query()->sole();
    $admin = User::query()->where('email', 'admin@example.com')->sole();

    $this->get(route('projects.index'))->assertSeeText('The Night Bus Atlas')->assertSeeText('The Museum of Missing Sounds');
    $this->actingAs($admin)->get(route('my.adoption-requests.index'))->assertSeeText($request->message);
    $this->patch(route('my.adoption-requests.approve', $request))->assertRedirect();
    $this->seed();

    expect($request->refresh()->revived_project_id)->not->toBeNull();
    expect(Project::query()->count())->toBe(3);
    $this->assertDatabaseCount('adoption_requests', 1);
});
