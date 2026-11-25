<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectUpdateRequest;
use App\Http\Requests\UpdateProjectUpdateRequest;
use App\Models\Project;
use App\Models\ProjectUpdate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class ProjectUpdateController extends Controller
{
    public function store(StoreProjectUpdateRequest $request, Project $project): RedirectResponse
    {
        $project->updates()->create([
            ...$request->validated(),
            'user_id' => $request->user()->getKey(),
        ]);

        return redirect()->route('my.projects.edit', $project)->with('status', 'Progress update published.');
    }

    public function update(UpdateProjectUpdateRequest $request, Project $project, ProjectUpdate $update): RedirectResponse
    {
        $update->update([
            'content' => $request->validated('updates.'.$update->getKey().'.content'),
        ]);

        return redirect()->route('my.projects.edit', $project)->with('status', 'Progress update saved.');
    }

    public function destroy(Project $project, ProjectUpdate $update): RedirectResponse
    {
        Gate::authorize('manageUpdates', $project);

        $update->delete();

        return redirect()->route('my.projects.edit', $project)->with('status', 'Progress update removed.');
    }
}
