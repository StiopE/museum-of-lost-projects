<x-layouts::app :title="__('Your museum home')">
    <div class="mx-auto w-full max-w-7xl space-y-8">
        <section class="flex flex-wrap items-end justify-between gap-6">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.18em] text-amber-400">The Museum of Failed Projects</p>
                <h1 class="mt-3 text-3xl font-bold text-white sm:text-4xl">Welcome back, {{ auth()->user()->name }}</h1>
                <p class="mt-3 max-w-2xl text-zinc-400">Pick up where you left off, share an unfinished idea, or explore projects looking for a second life.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('my.projects.create') }}" class="rounded-lg bg-amber-400 px-4 py-3 font-semibold text-zinc-950 hover:bg-amber-300">Add a project</a>
                <a href="{{ route('projects.index') }}" class="rounded-lg border border-zinc-600 px-4 py-3 font-semibold text-zinc-100 hover:bg-zinc-800">Explore the museum</a>
            </div>
        </section>

        <section class="grid gap-4 sm:grid-cols-3" aria-label="Project totals">
            @foreach ([['Total projects', $projectCounts['total']], ['Drafts', $projectCounts['drafts']], ['Published', $projectCounts['published']]] as [$label, $count])
                <article class="rounded-2xl border border-zinc-700 bg-zinc-900 p-5">
                    <p class="text-sm font-medium text-zinc-400">{{ $label }}</p>
                    <p class="mt-2 text-3xl font-bold text-white">{{ $count }}</p>
                </article>
            @endforeach
        </section>

        <section class="rounded-2xl border border-zinc-700 bg-zinc-900 p-6 sm:p-8">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 class="text-xl font-bold text-white">Your recent projects</h2>
                    <p class="mt-1 text-sm text-zinc-400">Your latest drafts and published stories.</p>
                </div>
                <a href="{{ route('my.projects.index') }}" class="text-sm font-semibold text-amber-400 hover:text-amber-300">View all projects →</a>
            </div>

            @if ($projects->isEmpty())
                <div class="mt-6 rounded-xl border border-dashed border-zinc-600 px-6 py-10 text-center">
                    <h3 class="text-lg font-semibold text-white">Your museum collection starts here</h3>
                    <p class="mt-2 text-sm text-zinc-400">Save an unfinished project as a draft, then publish it when you are ready.</p>
                    <a href="{{ route('my.projects.create') }}" class="mt-5 inline-flex rounded-lg bg-amber-400 px-4 py-2 font-semibold text-zinc-950 hover:bg-amber-300">Create your first project</a>
                </div>
            @else
                <div class="mt-5 divide-y divide-zinc-700">
                    @foreach ($projects as $project)
                        <article class="flex flex-wrap items-center justify-between gap-4 py-4 first:pt-0 last:pb-0">
                            <div>
                                <h3 class="font-semibold text-white">{{ $project->title }}</h3>
                                <p class="mt-1 text-sm text-zinc-400">{{ $project->category->name }} · Updated {{ $project->updated_at->diffForHumans() }}</p>
                            </div>
                            <div class="flex items-center gap-4">
                                <span class="rounded-full bg-zinc-800 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-zinc-300">{{ $project->status->value }}</span>
                                <a href="{{ route('my.projects.edit', $project) }}" class="text-sm font-semibold text-amber-400 hover:text-amber-300">Manage →</a>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
</x-layouts::app>
