<?php

namespace App\Http\Controllers;

use App\Enums\ProjectStatus;
use App\Http\Requests\PublishProjectRequest;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class ProjectController extends Controller
{
    public function store(StoreProjectRequest $request): RedirectResponse
    {
        $request->user()->projects()->create([
            ...$request->validated(),
            'status' => ProjectStatus::Draft,
            'adoption_available' => false,
        ]);

        return redirect()->route('dashboard')->with('status', 'Project saved as a draft.');
    }

    public function update(UpdateProjectRequest $request, Project $project): RedirectResponse
    {
        $project->update($request->validated());

        return redirect()->route('dashboard')->with('status', 'Project updated.');
    }

    public function publish(PublishProjectRequest $request, Project $project): RedirectResponse
    {
        $project->update([
            'status' => ProjectStatus::Published,
            'adoption_available' => $request->validated('adoption_available', false),
        ]);

        return redirect()->route('dashboard')->with('status', 'Project published.');
    }

    public function archive(Project $project): RedirectResponse
    {
        Gate::authorize('archive', $project);

        $project->update([
            'status' => ProjectStatus::Archived,
            'adoption_available' => false,
        ]);

        return redirect()->route('dashboard')->with('status', 'Project archived.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        Gate::authorize('delete', $project);

        $project->delete();

        return redirect()->route('dashboard')->with('status', 'Project deleted.');
    }
}
