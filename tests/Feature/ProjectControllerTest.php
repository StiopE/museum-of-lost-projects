<?php

use App\Enums\ProjectStatus;
use App\Models\Category;
use App\Models\Project;
use App\Models\User;

test('visitors only see published projects from the selected category', function () {
    $selectedCategory = Category::factory()->create();
    $otherCategory = Category::factory()->create();
    $selectedProject = Project::factory()->for($selectedCategory)->published()->create([
        'title' => 'Published in this category',
    ]);
    Project::factory()->for($otherCategory)->published()->create([
        'title' => 'Published elsewhere',
    ]);
    Project::factory()->for($selectedCategory)->create([
        'title' => 'Private draft',
    ]);

    $response = $this->get(route('projects.index', ['category' => $selectedCategory->slug]));

    $response->assertOk()
        ->assertSeeText($selectedProject->title)
        ->assertDontSeeText('Published elsewhere')
        ->assertDontSeeText('Private draft');
});

test('visitors cannot open a draft project page', function () {
    $project = Project::factory()->create();

    $this->get(route('projects.show', $project))
        ->assertNotFound();
});

test('project details safely render content written by a creator', function () {
    $dangerousText = "<script>alert('museum')</script>";
    $project = Project::factory()->published()->create([
        'title' => $dangerousText,
        'description' => $dangerousText,
        'story' => $dangerousText,
        'abandonment_reason' => $dangerousText,
        'original_goal' => $dangerousText,
        'what_went_wrong' => $dangerousText,
        'what_could_be_improved' => $dangerousText,
    ]);

    $response = $this->get(route('projects.show', $project));

    $response->assertOk()
        ->assertSeeHtml('&lt;script&gt;')
        ->assertDontSeeHtml('<script>alert');
});

test('a creator can save a project as a private draft', function () {
    $creator = User::factory()->create();
    $anotherUser = User::factory()->create();
    $category = Category::factory()->create();

    $response = $this->actingAs($creator)->post(route('my.projects.store'), [
        'user_id' => $anotherUser->id,
        'category_id' => $category->id,
        'title' => 'A promising abandoned idea',
        'description' => 'A short account of an unfinished idea.',
        'story' => 'The full story behind the project.',
        'abandonment_reason' => 'The creator ran out of time.',
        'original_goal' => 'Build something useful.',
        'what_went_wrong' => 'The first plan was too ambitious.',
        'what_could_be_improved' => 'Start with a smaller first version.',
        'status' => ProjectStatus::Published->value,
        'adoption_available' => true,
    ]);

    $createdProject = Project::query()->where('title', 'A promising abandoned idea')->firstOrFail();

    $response->assertRedirectToRoute('my.projects.edit', $createdProject);
    $this->assertDatabaseHas('projects', [
        'user_id' => $creator->id,
        'category_id' => $category->id,
        'title' => 'A promising abandoned idea',
        'status' => ProjectStatus::Draft->value,
        'adoption_available' => false,
    ]);
});

test('a project cannot be saved without its required story details', function () {
    $creator = User::factory()->create();

    $response = $this->actingAs($creator)->post(route('my.projects.store'), []);

    $response->assertSessionHasErrors([
        'category_id',
        'title',
        'description',
        'story',
        'abandonment_reason',
        'original_goal',
        'what_went_wrong',
        'what_could_be_improved',
    ]);
    $this->assertDatabaseCount('projects', 0);
});

test('a project description cannot exceed its published length limit', function () {
    $creator = User::factory()->create();
    $category = Category::factory()->create();

    $response = $this->actingAs($creator)->post(route('my.projects.store'), [
        'category_id' => $category->id,
        'title' => 'A project with an overlong description',
        'description' => str_repeat('a', 281),
        'story' => 'The full story behind the project.',
        'abandonment_reason' => 'The creator ran out of time.',
        'original_goal' => 'Build something useful.',
        'what_went_wrong' => 'The first plan was too ambitious.',
        'what_could_be_improved' => 'Start with a smaller first version.',
    ]);

    $response->assertSessionHasErrors('description');
    $this->assertDatabaseCount('projects', 0);
});

test('guests must sign in before opening their project workspace', function () {
    $this->get(route('my.projects.index'))
        ->assertRedirectToRoute('login');
});

test('signed-in users can sign out from the museum navigation', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('projects.index'))
        ->assertOk()
        ->assertSee('Log out')
        ->assertSee('action="'.route('logout').'"', false);

    $this->post(route('logout'))
        ->assertRedirectToRoute('home');

    $this->assertGuest();
});

test('a creator can publish a project and choose whether it is open for adoption', function () {
    $creator = User::factory()->create();
    $project = Project::factory()->for($creator, 'creator')->create();

    $response = $this->actingAs($creator)->patch(route('my.projects.publish', $project), [
        'adoption_available' => true,
    ]);

    $response->assertRedirectToRoute('my.projects.index');
    $this->assertDatabaseHas('projects', [
        'id' => $project->id,
        'status' => ProjectStatus::Published->value,
        'adoption_available' => true,
    ]);
});

test('a different user cannot edit or publish another creators project', function () {
    $creator = User::factory()->create();
    $otherUser = User::factory()->create();
    $project = Project::factory()->for($creator, 'creator')->create();

    $this->actingAs($otherUser)
        ->get(route('my.projects.edit', $project))
        ->assertForbidden();

    $this->actingAs($otherUser)
        ->patch(route('my.projects.publish', $project))
        ->assertForbidden();

    $this->assertDatabaseHas('projects', [
        'id' => $project->id,
        'status' => ProjectStatus::Draft->value,
    ]);
});

test('a creator can open and close requests without taking a project offline', function () {
    $project = Project::factory()->published()->create();
    $url = route('my.projects.adoption-availability', $project);
    $this->actingAs($project->creator)->get(route('my.projects.edit', $project))
        ->assertSeeText('Open to new requests')->assertSee($url, false);
    $this->patch($url, ['adoption_available' => 1])->assertRedirectToRoute('my.projects.edit', $project);
    expect($project->refresh()->adoption_available)->toBeTrue();
    $this->patch($url, ['adoption_available' => 0])->assertRedirectToRoute('my.projects.edit', $project);
    expect($project->refresh()->adoption_available)->toBeFalse();
    $this->get(route('projects.show', $project))->assertOk();
});

test('only managed published projects can have their availability changed', function () {
    $draft = Project::factory()->create();
    $url = route('my.projects.adoption-availability', $draft);
    $this->actingAs(User::factory()->create())->patch($url, ['adoption_available' => 1])->assertForbidden();
    $this->actingAs($draft->creator)->patch($url, ['adoption_available' => 1])->assertSessionHasErrors('adoption_available');
    $this->patch($url, ['adoption_available' => 'unknown'])->assertSessionHasErrors('adoption_available');
    expect($draft->refresh()->adoption_available)->toBeFalse();
});

test('visitors can combine the adoption and category filters', function () {
    $category = Category::factory()->create();
    $open = Project::factory()->for($category)->availableForAdoption()->create();
    $closed = Project::factory()->for($category)->published()->create();
    $draft = Project::factory()->for($category)->create(['adoption_available' => true]);
    $other = Project::factory()->availableForAdoption()->create();

    $this->get(route('projects.index', ['category' => $category->slug, 'adoption' => 'open']))
        ->assertOk()->assertSeeText($open->title)
        ->assertDontSeeText($closed->title)->assertDontSeeText($draft->title)->assertDontSeeText($other->title)
        ->assertSeeText('1 exhibit');
});
