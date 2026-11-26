<?php

namespace App\Http\Controllers;

use App\Actions\DeleteProjectImageFiles;
use App\Enums\AdoptionRequestStatus;
use App\Enums\ProjectStatus;
use App\Http\Requests\PublishProjectRequest;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateAdoptionAvailabilityRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Category;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function dashboard(Request $request): View
    {
        $user = $request->user();

        $projectCounts = [
            'total' => $user->projects()->count(),
            'drafts' => $user->projects()->where('status', ProjectStatus::Draft)->count(),
            'published' => $user->projects()->where('status', ProjectStatus::Published)->count(),
        ];

        $projects = $user->projects()
            ->with('category')
            ->latest('updated_at')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        return view('dashboard', [
            'projectCounts' => $projectCounts,
            'pendingRequestCount' => $user->receivedAdoptionRequests()->where('adoption_requests.status', AdoptionRequestStatus::Pending)->count(),
            'latestSentRequest' => $user->adoptionRequests()->with('project')->latest('updated_at')->orderByDesc('id')->first(),
            'projects' => $projects,
        ]);
    }

    public function index(Request $request): View
    {
        $categorySlug = $request->query('category');
        $category = is_string($categorySlug) && $categorySlug !== ''
            ? Category::query()->where('slug', $categorySlug)->firstOrFail()
            : null;

        $openOnly = $request->query('adoption') === 'open';
        $projects = Project::query()
            ->where('status', ProjectStatus::Published)
            ->when($openOnly, fn ($query) => $query->where('adoption_available', true))
            ->when($category, fn ($query) => $query->where('category_id', $category->id))
            ->with(['category', 'creator'])
            ->latest()
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        return view('projects.index', [
            'projects' => $projects,
            'categories' => Category::query()->orderBy('name')->get(),
            'selectedCategory' => $category,
            'openOnly' => $openOnly,
        ]);
    }

    public function show(Project $project): View
    {
        abort_unless($project->status === ProjectStatus::Published, 404);

        $project->load(['category', 'creator', 'images', 'revivedProjects' => fn ($query) => $query
            ->where('status', ProjectStatus::Published)
            ->with('creator')]);

        $adoptionRequest = auth()->check()
            ? $project->adoptionRequests()
                ->where('user_id', auth()->id())
                ->orderByDesc('id')
                ->first()
            : null;

        return view('projects.show', [
            'project' => $project,
            'adoptionRequest' => $adoptionRequest,
            'updates' => $project->updates()
                ->with('author')
                ->latest()
                ->paginate(10, ['*'], 'updates_page'),
        ]);
    }

    public function myIndex(Request $request): View
    {
        $projects = $request->user()->projects()
            ->with('category')
            ->latest()
            ->paginate(12);

        return view('my.projects.index', ['projects' => $projects]);
    }

    public function create(): View
    {
        return view('my.projects.create', [
            'categories' => Category::query()->orderBy('name')->get(),
        ]);
    }

    public function edit(Project $project): View
    {
        Gate::authorize('update', $project);

        $project->load(['category', 'images', 'parentProject', 'adoptionRequests.requester', 'adoptionRequests.revivedProject']);
        $updates = $project->updates()
            ->with('author')
            ->latest()
            ->paginate(10, ['*'], 'updates_page');

        return view('my.projects.edit', [
            'project' => $project,
            'categories' => Category::query()->orderBy('name')->get(),
            'updates' => $updates,
        ]);
    }

    public function store(StoreProjectRequest $request): RedirectResponse
    {
        $project = $request->user()->projects()->create([
            ...$request->validated(),
            'status' => ProjectStatus::Draft,
            'adoption_available' => false,
        ]);

        return redirect()->route('my.projects.edit', $project)->with('status', 'Project saved as a draft.');
    }

    public function update(UpdateProjectRequest $request, Project $project): RedirectResponse
    {
        $project->update($request->validated());

        return redirect()->route('my.projects.index')->with('status', 'Project updated.');
    }

    public function publish(PublishProjectRequest $request, Project $project): RedirectResponse
    {
        $project->update([
            'status' => ProjectStatus::Published,
            'adoption_available' => $request->validated('adoption_available', false),
        ]);

        return redirect()->route('my.projects.index')->with('status', 'Project published.');
    }

    public function updateAdoptionAvailability(UpdateAdoptionAvailabilityRequest $request, Project $project): RedirectResponse
    {
        $changed = Project::query()->whereKey($project->id)->where('status', ProjectStatus::Published)
            ->update(['adoption_available' => $request->boolean('adoption_available')]);

        if (! $changed && $project->fresh()?->status !== ProjectStatus::Published) {
            return back()->withErrors(['adoption_available' => 'Publish the project before changing its adoption availability.']);
        }

        return redirect()->route('my.projects.edit', $project)->with('status', 'Adoption availability saved.');
    }

    public function archive(Project $project): RedirectResponse
    {
        Gate::authorize('archive', $project);

        $project->update([
            'status' => ProjectStatus::Archived,
            'adoption_available' => false,
        ]);

        return redirect()->route('my.projects.index')->with('status', 'Project archived.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        DB::transaction(function () use ($project): void {
            $project = Project::query()->lockForUpdate()->findOrFail($project->id);
            Gate::authorize('delete', $project);

            $images = $project->images()->get();
            $project->delete();
            DeleteProjectImageFiles::afterDeletion($images);
        });

        return redirect()->route('my.projects.index')->with('status', 'Project deleted.');
    }
}
