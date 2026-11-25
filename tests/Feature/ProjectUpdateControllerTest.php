<?php

use App\Models\Project;
use App\Models\ProjectUpdate;
use App\Models\User;

test('a project owner can publish a progress update that appears on the public page', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $project = Project::factory()->for($owner, 'creator')->published()->create();

    $response = $this->actingAs($owner)->post(route('my.projects.updates.store', $project), [
        'user_id' => $otherUser->id,
        'content' => 'The first working version is now complete.',
    ]);

    $response->assertRedirectToRoute('my.projects.edit', $project);
    $this->assertDatabaseHas('project_updates', [
        'project_id' => $project->id,
        'user_id' => $owner->id,
        'content' => 'The first working version is now complete.',
    ]);
    $this->get(route('projects.show', $project))
        ->assertOk()
        ->assertSeeText('The first working version is now complete.');
});

test('an administrator cannot write progress updates to another users project', function () {
    $owner = User::factory()->create();
    $administrator = User::factory()->create();
    $administrator->forceFill(['is_admin' => true])->save();
    $project = Project::factory()->for($owner, 'creator')->published()->create();

    $this->actingAs($administrator)
        ->post(route('my.projects.updates.store', $project), [
            'content' => 'The curator added a moderation note.',
        ])
        ->assertForbidden();

    $this->assertDatabaseCount('project_updates', 0);
});

test('a project owner can edit their progress update', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $project = Project::factory()->for($owner, 'creator')->published()->create();
    $update = ProjectUpdate::factory()->for($project)->create();

    $this->actingAs($owner)
        ->get(route('my.projects.edit', $project))
        ->assertOk()
        ->assertSee($update->content)
        ->assertSee('name="updates['.$update->id.'][content]"', false);

    $response = $this->actingAs($owner)->put(route('my.projects.updates.update', [$project, $update]), [
        'user_id' => $otherUser->id,
        'project_id' => $otherUser->id,
        'updates' => [
            $update->id => ['content' => 'The project is now ready for early feedback.'],
        ],
    ]);

    $response->assertRedirectToRoute('my.projects.edit', $project);
    $this->assertDatabaseHas('project_updates', [
        'id' => $update->id,
        'project_id' => $project->id,
        'user_id' => $owner->id,
        'content' => 'The project is now ready for early feedback.',
    ]);

    $invalidUpdate = $this->actingAs($owner)->put(route('my.projects.updates.update', [$project, $update]), [
        'updates' => [
            $update->id => ['content' => str_repeat('a', 10001)],
        ],
    ]);

    $invalidUpdate->assertSessionHasErrors('updates.'.$update->id.'.content');
    $this->assertDatabaseHas('project_updates', [
        'id' => $update->id,
        'content' => 'The project is now ready for early feedback.',
    ]);
});

test('a project owner can delete their progress update', function () {
    $owner = User::factory()->create();
    $project = Project::factory()->for($owner, 'creator')->published()->create();
    $update = ProjectUpdate::factory()->for($project)->create();

    $response = $this->actingAs($owner)->delete(route('my.projects.updates.destroy', [$project, $update]));

    $response->assertRedirectToRoute('my.projects.edit', $project);
    $this->assertDatabaseMissing('project_updates', ['id' => $update->id]);
});

test('another user cannot create edit or delete project progress updates', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $project = Project::factory()->for($owner, 'creator')->published()->create();
    $update = ProjectUpdate::factory()->for($project)->create();

    $this->actingAs($otherUser)
        ->post(route('my.projects.updates.store', $project), ['content' => 'Unauthorized update.'])
        ->assertForbidden();

    $this->actingAs($otherUser)
        ->put(route('my.projects.updates.update', [$project, $update]), [
            'updates' => [$update->id => ['content' => 'Unauthorized edit.']],
        ])
        ->assertForbidden();

    $this->actingAs($otherUser)
        ->delete(route('my.projects.updates.destroy', [$project, $update]))
        ->assertForbidden();

    $this->assertDatabaseHas('project_updates', [
        'id' => $update->id,
        'content' => $update->content,
    ]);
    $this->assertDatabaseCount('project_updates', 1);
});

test('an update cannot be edited through a different project URL', function () {
    $owner = User::factory()->create();
    $project = Project::factory()->for($owner, 'creator')->published()->create();
    $otherProject = Project::factory()->for($owner, 'creator')->published()->create();
    $update = ProjectUpdate::factory()->for($otherProject)->create();

    $this->actingAs($owner)
        ->put(route('my.projects.updates.update', [$project, $update]), ['content' => 'Wrong project.'])
        ->assertNotFound();

    $this->assertDatabaseHas('project_updates', [
        'id' => $update->id,
        'project_id' => $otherProject->id,
    ]);
});

test('a progress update must include content within the allowed length', function () {
    $owner = User::factory()->create();
    $project = Project::factory()->for($owner, 'creator')->create();

    $missingContent = $this->actingAs($owner)->post(route('my.projects.updates.store', $project), []);
    $missingContent->assertSessionHasErrors('content');

    $tooLong = $this->actingAs($owner)->post(route('my.projects.updates.store', $project), [
        'content' => str_repeat('a', 10001),
    ]);
    $tooLong->assertSessionHasErrors('content');

    $this->assertDatabaseCount('project_updates', 0);
});

test('public project pages escape creator-written progress updates', function () {
    $owner = User::factory()->create();
    $project = Project::factory()->for($owner, 'creator')->published()->create();
    ProjectUpdate::factory()->for($project)->create([
        'content' => '<script>alert("museum")</script>',
    ]);

    $this->get(route('projects.show', $project))
        ->assertOk()
        ->assertSeeHtml('&lt;script&gt;')
        ->assertDontSeeHtml('<script>alert');
});

test('guests are redirected before posting project progress updates', function () {
    $owner = User::factory()->create();
    $project = Project::factory()->for($owner, 'creator')->create();

    $this->post(route('my.projects.updates.store', $project), [
        'content' => 'A guest should not be able to post this.',
    ])->assertRedirectToRoute('login');

    $this->assertDatabaseCount('project_updates', 0);
});
test('example', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});
