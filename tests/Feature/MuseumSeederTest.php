<?php

use App\Enums\ProjectStatus;
use App\Models\AdoptionRequest;
use App\Models\Project;
use App\Models\ProjectImage;
use App\Models\ProjectUpdate;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

test('seeding provides an explorable collection and a reviewable request without duplicating demo work', function () {
    Storage::fake('local');
    $this->seed();
    $atlas = Project::query()->where('title', 'The Night Bus Atlas')->sole();
    $request = $atlas->adoptionRequests()->sole();
    $admin = User::query()->where('email', 'admin@example.com')->sole();

    $this->get(route('projects.index'))->assertSeeText('The Night Bus Atlas')->assertSeeText('The Museum of Missing Sounds');
    $this->actingAs($admin)->get(route('my.adoption-requests.index'))->assertSeeText($request->message);
    $this->patch(route('my.adoption-requests.approve', $request))->assertRedirect();
    $this->seed();

    expect($request->refresh()->revived_project_id)->not->toBeNull();
    expect($atlas->revivedProjects()->count())->toBe(1);
    expect($atlas->adoptionRequests()->count())->toBe(1);
});

test('the demo gallery has dated stories across every category and a published revival with its own author', function () {
    Storage::fake('local');
    $this->seed();

    $public = Project::query()->where('status', ProjectStatus::Published);
    expect($public->count())->toBeGreaterThan(12);
    expect($public->distinct()->count('category_id'))->toBe(5);
    $this->get(route('projects.index', ['page' => 2]))->assertOk()->assertDontSeeText('Carbon Paper Weather');
    $draft = Project::query()->where('title', 'Carbon Paper Weather')->sole();
    $this->get(route('projects.show', $draft))->assertNotFound();

    $original = Project::query()->where('title', 'Tide Table Loom')->sole();
    $revival = $original->revivedProjects()->sole();
    $approval = $original->adoptionRequests()->sole();
    expect($approval->revived_project_id)->toBe($revival->id)
        ->and($approval->user_id)->toBe($revival->user_id)
        ->and($original->user_id)->not->toBe($revival->user_id);
    $this->get(route('projects.show', $original))->assertSeeText($revival->title);
    $this->get(route('projects.show', $revival))->assertSeeText('Noa Field')->assertSeeText('The second sample fits in an envelope.');

    foreach (ProjectImage::all() as $image) {
        $this->get(route('projects.images.show', [$image->project_id, $image]))->assertOk()->assertHeader('Content-Type', 'image/png');
    }
    expect(ProjectImage::count())->toBe(2);
});

test('rerunning the demo import preserves edits and does not duplicate community activity or files', function () {
    $disk = Storage::fake('local');
    $this->seed();
    $models = [User::class, Project::class, ProjectUpdate::class, AdoptionRequest::class, ProjectImage::class];
    $counts = array_map(fn (string $model): int => $model::count(), $models);
    $files = $disk->allFiles();
    $project = Project::query()->where('title', 'Tide Table Loom')->sole();
    $project->update(['title' => 'A second edition']);

    $this->seed();

    expect(array_map(fn (string $model): int => $model::count(), $models))->toBe($counts)
        ->and($disk->allFiles())->toBe($files)
        ->and($project->refresh()->title)->toBe('A second edition');
});
