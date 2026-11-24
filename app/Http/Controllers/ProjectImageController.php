<?php

namespace App\Http\Controllers;

use App\Enums\ProjectStatus;
use App\Http\Requests\StoreProjectImagesRequest;
use App\Models\Project;
use App\Models\ProjectImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProjectImageController extends Controller
{
    public function store(StoreProjectImagesRequest $request, Project $project): RedirectResponse
    {
        $sortOrder = ($project->images()->max('sort_order') ?? -1) + 1;

        foreach ($request->validated('images') as $imageFile) {
            $path = $imageFile->store('project-images', 'local');

            if (! is_string($path)) {
                abort(500, 'The image could not be stored.');
            }

            $project->images()->create([
                'disk' => 'local',
                'image_path' => $path,
                'sort_order' => $sortOrder++,
            ]);
        }

        return redirect()->route('my.projects.edit', $project)->with('status', 'Project images uploaded.');
    }

    public function show(Project $project, ProjectImage $image): StreamedResponse
    {
        abort_unless($image->project_id === $project->getKey(), 404);
        abort_unless(
            $project->status === ProjectStatus::Published
                || (auth()->check() && auth()->user()->can('view', $project)),
            404,
        );

        return Storage::disk($image->disk)->response($image->image_path);
    }

    public function destroy(Project $project, ProjectImage $image): RedirectResponse
    {
        Gate::authorize('update', $project);
        abort_unless($image->project_id === $project->getKey(), 404);

        Storage::disk($image->disk)->delete($image->image_path);
        $image->delete();

        return redirect()->route('my.projects.edit', $project)->with('status', 'Project image removed.');
    }
}
