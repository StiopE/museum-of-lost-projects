<?php

namespace App\Http\Controllers;

use App\Enums\ProjectStatus;
use App\Http\Requests\PublishProjectRequest;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Category;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(Request $request): View
    {
        $categorySlug = $request->query('category');
        $category = is_string($categorySlug) && $categorySlug !== ''
            ? Category::query()->where('slug', $categorySlug)->firstOrFail()
            : null;

        $projects = Project::query()
            ->where('status', ProjectStatus::Published)
            ->when($category, fn ($query) => $query->where('category_id', $category->id))
            ->with(['category', 'creator'])
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('projects.index', [
            'projects' => $projects,
            'categories' => Category::query()->orderBy('name')->get(),
            'selectedCategory' => $category,
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
                ->latest()
                ->first()
            : null;

        return view('projects.show', [
            'project' => $project,
            'adoptionRequest' => $adoptionRequest,
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

        $project->load(['category', 'images', 'adoptionRequests.requester', 'adoptionRequests.revivedProject']);

        return view('my.projects.edit', [
            'project' => $project,
            'categories' => Category::query()->orderBy('name')->get(),
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
        Gate::authorize('delete', $project);

        $project->delete();

        return redirect()->route('my.projects.index')->with('status', 'Project deleted.');
    }
}
