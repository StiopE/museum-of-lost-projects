<?php

use App\Actions\DeleteProjectImageFiles;
use App\Models\Project;
use App\Models\ProjectImage;
use App\Models\User;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

test('a creator can upload an image that is available only to them while the project is a draft', function () {
    Storage::fake('local');
    $creator = User::factory()->create();
    $project = Project::factory()->for($creator, 'creator')->create();

    $response = $this->actingAs($creator)->post(route('my.projects.images.store', $project), [
        'images' => [UploadedFile::fake()->image('project.png', 100, 100)],
    ]);

    $response->assertRedirectToRoute('my.projects.edit', $project);
    $image = ProjectImage::query()->whereBelongsTo($project)->firstOrFail();
    expect($image->disk)->toBe('local');
    Storage::disk('local')->assertExists($image->image_path);

    $this->actingAs($creator)
        ->get(route('my.projects.edit', $project))
        ->assertOk()
        ->assertSee('name="images[]"', false);

    $this->actingAs($creator)
        ->get(route('projects.images.show', [$project, $image]))
        ->assertOk()
        ->assertHeader('Content-Type', 'image/png');

    auth()->logout();
    $this->get(route('projects.images.show', [$project, $image]))
        ->assertNotFound();
});

test('an uploaded image is publicly viewable after its project is published', function () {
    Storage::fake('local');
    $creator = User::factory()->create();
    $project = Project::factory()->for($creator, 'creator')->create();
    $path = UploadedFile::fake()->image('project.jpg', 100, 100)->store('project-images', 'local');
    $image = ProjectImage::factory()->for($project)->create([
        'disk' => 'local',
        'image_path' => $path,
    ]);
    $project->update(['status' => 'published']);

    $this->get(route('projects.images.show', [$project, $image]))
        ->assertOk()
        ->assertHeader('Content-Type', 'image/jpeg');

    $this->get(route('projects.show', $project))
        ->assertOk()
        ->assertSee(route('projects.images.show', [$project, $image]), false);
});

test('a creator cannot upload a non-image file', function () {
    Storage::fake('local');
    $creator = User::factory()->create();
    $project = Project::factory()->for($creator, 'creator')->create();

    $response = $this->actingAs($creator)->post(route('my.projects.images.store', $project), [
        'images' => [UploadedFile::fake()->create('notes.txt', 100, 'text/plain')],
    ]);

    $response->assertSessionHasErrors('images.0');
    $this->assertDatabaseCount('project_images', 0);
    expect(Storage::disk('local')->allFiles())->toBe([]);
});

test('a creator cannot upload an image larger than five megabytes', function () {
    Storage::fake('local');
    $creator = User::factory()->create();
    $project = Project::factory()->for($creator, 'creator')->create();

    $response = $this->actingAs($creator)->post(route('my.projects.images.store', $project), [
        'images' => [UploadedFile::fake()->image('large.png', 100, 100)->size(5121)],
    ]);

    $response->assertSessionHasErrors('images.0');
    $this->assertDatabaseCount('project_images', 0);
    expect(Storage::disk('local')->allFiles())->toBe([]);
});

test('a creator cannot upload an image with excessive dimensions', function () {
    Storage::fake('local');
    $creator = User::factory()->create();
    $project = Project::factory()->for($creator, 'creator')->create();

    $response = $this->actingAs($creator)->post(route('my.projects.images.store', $project), [
        'images' => [UploadedFile::fake()->image('wide.png', 4001, 10)],
    ]);

    $response->assertSessionHasErrors('images.0');
    $this->assertDatabaseCount('project_images', 0);
    expect(Storage::disk('local')->allFiles())->toBe([]);
});

test('a user cannot upload images to another creators project', function () {
    Storage::fake('local');
    $creator = User::factory()->create();
    $otherUser = User::factory()->create();
    $project = Project::factory()->for($creator, 'creator')->create();

    $this->actingAs($otherUser)
        ->post(route('my.projects.images.store', $project), [
            'images' => [UploadedFile::fake()->image('project.png', 100, 100)],
        ])
        ->assertForbidden();

    $this->assertDatabaseCount('project_images', 0);
    expect(Storage::disk('local')->allFiles())->toBe([]);
});

test('a creator can remove an image from their project', function () {
    Storage::fake('local');
    $creator = User::factory()->create();
    $project = Project::factory()->for($creator, 'creator')->create();
    $path = UploadedFile::fake()->image('project.png', 100, 100)->store('project-images', 'local');
    $image = ProjectImage::factory()->for($project)->create([
        'disk' => 'local',
        'image_path' => $path,
    ]);

    $response = $this->actingAs($creator)->delete(route('my.projects.images.destroy', [$project, $image]));

    $response->assertRedirectToRoute('my.projects.edit', $project);
    $this->assertDatabaseMissing('project_images', ['id' => $image->id]);
    Storage::disk('local')->assertMissing($path);
});

test('a different user cannot remove an image from the project', function () {
    Storage::fake('local');
    $creator = User::factory()->create();
    $otherUser = User::factory()->create();
    $project = Project::factory()->for($creator, 'creator')->create();
    $path = UploadedFile::fake()->image('project.png', 100, 100)->store('project-images', 'local');
    $image = ProjectImage::factory()->for($project)->create([
        'disk' => 'local',
        'image_path' => $path,
    ]);

    $this->actingAs($otherUser)
        ->delete(route('my.projects.images.destroy', [$project, $image]))
        ->assertForbidden();

    $this->assertDatabaseHas('project_images', ['id' => $image->id]);
    Storage::disk('local')->assertExists($path);
});

test('a project cannot have more than six uploaded images', function () {
    Storage::fake('local');
    $creator = User::factory()->create();
    $project = Project::factory()->for($creator, 'creator')->create();
    ProjectImage::factory()->for($project)->count(6)->create();

    $response = $this->actingAs($creator)->post(route('my.projects.images.store', $project), [
        'images' => [UploadedFile::fake()->image('seventh.png', 100, 100)],
    ]);

    $response->assertSessionHasErrors('images');
    $this->assertDatabaseCount('project_images', 6);
    expect(Storage::disk('local')->allFiles())->toBe([]);
});

test('a nested image URL cannot access an image belonging to another project', function () {
    Storage::fake('local');
    $project = Project::factory()->published()->create();
    $otherProject = Project::factory()->published()->create();
    $path = UploadedFile::fake()->image('project.png', 100, 100)->store('project-images', 'local');
    $image = ProjectImage::factory()->for($otherProject)->create([
        'disk' => 'local',
        'image_path' => $path,
    ]);

    $this->get(route('projects.images.show', [$project, $image]))
        ->assertNotFound();
});

test('deleting a project removes its uploads from each recorded disk', function () {
    $project = Project::factory()->create();
    foreach (['local', 'public'] as $disk) {
        Storage::fake($disk)->put('project-images/photo.jpg', 'image');
        ProjectImage::factory()->for($project)->create(['disk' => $disk, 'image_path' => 'project-images/photo.jpg']);
    }

    $this->actingAs(User::factory()->create())->delete(route('my.projects.destroy', $project))->assertForbidden();
    Storage::disk('local')->assertExists('project-images/photo.jpg');
    $this->actingAs($project->creator)->delete(route('my.projects.destroy', $project))->assertRedirectToRoute('my.projects.index');

    Storage::disk('local')->assertMissing('project-images/photo.jpg');
    Storage::disk('public')->assertMissing('project-images/photo.jpg');
    $this->assertModelMissing($project);
});

test('a failed disk cleanup is queued with the paths needed for a retry', function () {
    $image = ProjectImage::factory()->create(['disk' => 'local']);
    Queue::fake();
    $disk = Mockery::mock(Filesystem::class);
    $disk->shouldReceive('delete')->once()->with([$image->image_path])->andReturn(false);
    Storage::set('local', $disk);

    $this->actingAs($image->project->creator)->delete(route('my.projects.destroy', $image->project))->assertRedirect();

    Storage::fake('local')->put($image->image_path, 'image');
    Queue::assertPushed(DeleteProjectImageFiles::class, function (DeleteProjectImageFiles $job) use ($image): bool {
        $job->handle();

        return Storage::disk('local')->missing($image->image_path);
    });
});
