@extends('layouts.museum')
@section('title', 'Your museum home | Museum of Failed Projects')
@section('content')
<section class="mx-auto max-w-6xl space-y-8 px-6 py-12">
    <div class="flex flex-wrap items-end justify-between gap-6">
        <div>
            <p class="text-sm font-semibold uppercase tracking-widest text-amber-700">The Museum of Failed Projects</p>
            <h1 class="mt-3 text-3xl font-bold sm:text-4xl">Welcome back, {{ auth()->user()->name }}</h1>
            <p class="mt-3 max-w-2xl text-zinc-600">Pick up where you left off, share an unfinished idea, or explore projects looking for a second life.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('my.projects.create') }}" class="rounded-lg bg-zinc-900 px-4 py-3 font-semibold text-white hover:bg-zinc-700">Add a project</a>
            <a href="{{ route('projects.index') }}" class="rounded-lg border border-stone-300 px-4 py-3 font-semibold hover:bg-white">Explore the museum</a>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-3" aria-label="Project totals">
        @foreach ([['Total projects', $projectCounts['total']], ['Drafts', $projectCounts['drafts']], ['Published', $projectCounts['published']]] as [$label, $count])
            <article class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-zinc-600">{{ $label }}</p>
                <p class="mt-2 text-3xl font-bold">{{ $count }}</p>
            </article>
        @endforeach
    </div>

    <section class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm sm:p-8">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h2 class="text-xl font-bold">Your recent projects</h2>
                <p class="mt-1 text-sm text-zinc-600">Your latest drafts and published stories.</p>
            </div>
            <a href="{{ route('my.projects.index') }}" class="text-sm font-semibold text-amber-800 hover:text-amber-700">View all projects →</a>
        </div>

        @if ($projects->isEmpty())
            <div class="mt-6 rounded-xl border border-dashed border-stone-300 px-6 py-10 text-center">
                <h3 class="text-lg font-semibold">Your museum collection starts here</h3>
                <p class="mt-2 text-sm text-zinc-600">Save an unfinished project as a draft, then publish it when you are ready.</p>
                <a href="{{ route('my.projects.create') }}" class="mt-5 inline-flex rounded-lg bg-zinc-900 px-4 py-2 font-semibold text-white hover:bg-zinc-700">Create your first project</a>
            </div>
        @else
            <div class="mt-5 divide-y divide-stone-200">
                @foreach ($projects as $project)
                    <article class="flex flex-wrap items-center justify-between gap-4 py-4 first:pt-0 last:pb-0">
                        <div>
                            <h3 class="font-semibold">{{ $project->title }}</h3>
                            <p class="mt-1 text-sm text-zinc-600">{{ $project->category->name }} · Updated {{ $project->updated_at->diffForHumans() }}</p>
                        </div>
                        <div class="flex items-center gap-4">
                            <span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-zinc-700">{{ $project->status->value }}</span>
                            <a href="{{ route('my.projects.edit', $project) }}" class="text-sm font-semibold text-amber-800 hover:text-amber-700">Manage →</a>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
</section>
@endsection
