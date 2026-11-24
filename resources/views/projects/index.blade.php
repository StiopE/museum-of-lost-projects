@extends('layouts.museum')
@section('title', 'Explore | Museum of Failed Projects')
@section('content')
<section class="mx-auto max-w-6xl px-6 py-12">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div><p class="text-sm font-semibold uppercase tracking-widest text-amber-700">The collection</p><h1 class="mt-2 text-4xl font-bold">Projects worth remembering</h1><p class="mt-3 text-zinc-600">Browse unfinished projects and the stories behind them.</p></div>
        @auth<a href="{{ route('my.projects.create') }}" class="rounded-lg bg-zinc-900 px-4 py-2 text-white">Submit a project</a>@else<a href="{{ route('register') }}" class="rounded-lg bg-zinc-900 px-4 py-2 text-white">Submit a project</a>@endauth
    </div>
    <div class="mt-8 flex flex-wrap gap-2">
        <a href="{{ route('projects.index') }}" class="rounded-full border px-4 py-2 text-sm {{ !$selectedCategory ? 'border-zinc-900 bg-zinc-900 text-white' : 'border-stone-300' }}">All categories</a>
        @foreach ($categories as $category)
            <a href="{{ route('projects.index', ['category' => $category->slug]) }}" class="rounded-full border px-4 py-2 text-sm {{ $selectedCategory?->is($category) ? 'border-zinc-900 bg-zinc-900 text-white' : 'border-stone-300' }}">{{ $category->name }}</a>
        @endforeach
    </div>
    @if ($projects->isEmpty())
        <div class="mt-10 rounded-2xl border border-dashed border-stone-300 p-12 text-center"><h2 class="text-xl font-semibold">The galleries are waiting</h2><p class="mt-2 text-zinc-600">There are no published projects here yet.</p></div>
    @else
        <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($projects as $project)
                <a href="{{ route('projects.show', $project) }}" class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-md">
                    <span class="text-sm font-medium text-amber-700">{{ $project->category->name }}</span>
                    <h2 class="mt-3 text-xl font-bold">{{ $project->title }}</h2>
                    <p class="mt-3 line-clamp-3 text-sm leading-6 text-zinc-600">{{ $project->description }}</p>
                    <p class="mt-5 text-xs text-zinc-500">Shared by {{ $project->creator->name }}</p>
                </a>
            @endforeach
        </div>
        <div class="mt-8">{{ $projects->links() }}</div>
    @endif
</section>
@endsection
