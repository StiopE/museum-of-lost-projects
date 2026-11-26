<?php

namespace App\Http\Controllers;

use App\Enums\ProjectStatus;
use App\Models\AdoptionRequest;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ProjectReviewController extends Controller
{
    public function index(): View
    {
        return view('my.reviews', ['projects' => Project::query()->where('status', ProjectStatus::Submitted)->with('creator')->oldest()->paginate(20),
            'requests' => AdoptionRequest::query()->where('status', 'pending')->with('project', 'requester')->oldest()->paginate(20, ['*'], 'requests_page')]);
    }

    public function submit(Project $project): RedirectResponse
    {
        Gate::authorize('update', $project);
        $changed = Project::query()->whereKey($project->id)->where('status', ProjectStatus::Draft)->update(['status' => ProjectStatus::Submitted]);
        abort_unless($changed === 1, 409);

        return back()->with('status', 'Sent to the curator.');
    }

    public function decide(Request $request, Project $project): RedirectResponse
    {
        $request->validate(['decision' => ['required', 'in:publish,return']]);
        $status = $request->input('decision') === 'publish' ? ProjectStatus::Published : ProjectStatus::Draft;
        $changed = Project::query()->whereKey($project->id)->where('status', ProjectStatus::Submitted)->update(['status' => $status, 'adoption_available' => false]);
        abort_unless($changed === 1, 409);

        return redirect()->route('admin.reviews')->with('status', $status === ProjectStatus::Draft ? 'Returned to the creator’s drafts.' : 'Exhibit published.');
    }
}
