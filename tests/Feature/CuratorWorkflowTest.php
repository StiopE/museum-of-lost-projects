<?php

use App\Enums\ProjectStatus;
use App\Models\AdoptionRequest;
use App\Models\Category;
use App\Models\Project;
use App\Models\ProjectImage;
use App\Models\User;

test('curators maintain categories and cannot delete an occupied shelf', function () {
    $this->actingAs(User::factory()->create())->get(route('admin.categories.index'))->assertForbidden();
    $this->actingAs(User::factory()->create(['is_admin' => true]));
    $fields = ['name' => 'Ceramics', 'slug' => 'ceramics'];
    $this->get(route('admin.categories.create'))->assertOk();
    $this->post(route('admin.categories.store'), $fields)->assertSessionHasNoErrors();
    $category = Category::where('slug', 'ceramics')->sole();
    $this->get(route('admin.categories.index'))->assertSeeText('Ceramics');
    $this->get(route('admin.categories.edit', $category))->assertOk();
    $this->put(route('admin.categories.update', $category), [...$fields, 'name' => 'Clay'])->assertSessionHasNoErrors();
    $this->post(route('admin.categories.store'), $fields)->assertSessionHasErrors('slug');
    Project::factory()->for($category)->create();
    $this->delete(route('admin.categories.destroy', $category))->assertRedirect();
    expect($category->fresh()->name)->toBe('Clay');
    $unused = Category::factory()->create();
    $this->delete(route('admin.categories.destroy', $unused))->assertRedirect();
    $this->assertModelMissing($unused);
});

test('only curators decide on submitted drafts', function () {
    $project = Project::factory()->create();
    $owner = $project->creator;
    $admin = User::factory()->create(['is_admin' => true]);
    $this->actingAs($owner)->patch(route('my.projects.submit', $project))->assertRedirect();
    $this->get(route('projects.show', $project))->assertNotFound();
    $this->patch(route('my.projects.publish', $project))->assertForbidden();
    $decision = route('admin.reviews.decide', $project);
    $this->patch($decision, ['decision' => 'publish'])->assertForbidden();
    $this->actingAs($admin)->get(route('admin.reviews'))->assertSeeText($project->title);
    $this->patch($decision, ['decision' => 'return'])->assertRedirect();
    expect($project->fresh()->status)->toBe(ProjectStatus::Draft);
    $this->actingAs($owner)->patch(route('my.projects.submit', $project))->assertRedirect();
    $this->actingAs($admin)->patch($decision, ['decision' => 'publish'])->assertRedirect();
    $this->patch($decision, ['decision' => 'return'])->assertConflict();
    $this->get(route('projects.show', $project))->assertOk();
});

test('image edits enforce ownership, parent scope and valid display order', function () {
    $image = ProjectImage::factory()->create();
    $project = $image->project;
    $url = route('my.projects.images.update', [$project, $image]);
    $fields = ['caption' => 'Front elevation', 'sort_order' => 2];
    $this->actingAs(User::factory()->create())->put($url, $fields)->assertForbidden();
    $this->actingAs($project->creator)->put(route('my.projects.images.update', [Project::factory()->create(), $image]), $fields)->assertNotFound();
    $this->put($url, [...$fields, 'sort_order' => -1])->assertSessionHasErrorsIn('image'.$image->id, 'sort_order');
    $this->put($url, $fields)->assertSessionHasNoErrors();
    expect($image->fresh()->caption)->toBe('Front elevation')->and($image->fresh()->sort_order)->toBe(2);
});

test('a curator can resolve another creators request but cannot approve their own proposal', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $project = Project::factory()->availableForAdoption()->create();
    $own = AdoptionRequest::factory()->for($project)->for($admin, 'requester')->create();
    $other = AdoptionRequest::factory()->for($project)->create();
    $this->actingAs($admin)->patch(route('my.adoption-requests.approve', $own))->assertForbidden();
    $this->get(route('admin.reviews'))->assertSeeText($project->title);
    $this->patch(route('my.adoption-requests.approve', $other))->assertRedirect();
    expect($other->fresh()->revived_project_id)->not->toBeNull();
});
