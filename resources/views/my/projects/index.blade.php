@extends('layouts.museum')
@section('title', 'My Projects')
@section('content')
<section class="mx-auto max-w-6xl px-6 py-12">
    <div class="flex flex-wrap items-end justify-between gap-4"><div><p class="font-semibold uppercase tracking-widest text-amber-700">Your workspace</p><h1 class="mt-2 text-4xl font-bold">My Projects</h1><p class="mt-3 text-zinc-600">Draft, publish, and manage the stories you have shared.</p></div><a href="{{ route('my.projects.create') }}" class="rounded-lg bg-zinc-900 px-4 py-3 font-semibold text-white">Add a project</a></div>
    @if ($projects->isEmpty())
        <div class="mt-8 rounded-2xl border border-dashed border-stone-300 p-12 text-center"><h2 class="text-xl font-semibold">Your collection starts here</h2><p class="mt-2 text-zinc-600">Add a project and save it as a draft to get started.</p><a href="{{ route('my.projects.create') }}" class="mt-5 inline-block font-semibold text-amber-800">Create your first project →</a></div>
    @else
        <div class="mt-8 space-y-4">@foreach ($projects as $project)<article class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border bg-white p-5"><div><div class="flex flex-wrap items-center gap-3"><h2 class="text-xl font-bold">{{ $project->title }}</h2><span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-semibold uppercase">{{ $project->parent_project_id ? 'Revived · ' : '' }}{{ $project->status->value }}</span></div><p class="mt-2 text-sm text-zinc-600">{{ $project->category->name }} · Updated {{ $project->updated_at->diffForHumans() }}</p></div><div class="flex flex-wrap items-center gap-3">@if ($project->status->value === 'published')<a class="font-medium text-amber-800" href="{{ route('projects.show', $project) }}">View page</a>@endif<a class="rounded-lg border px-4 py-2 font-medium" href="{{ route('my.projects.edit', $project) }}">Manage</a></div></article>@endforeach</div>
        <div class="mt-8">{{ $projects->links() }}</div>
    @endif
</section>
@endsection
