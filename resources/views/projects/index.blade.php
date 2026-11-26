@extends('layouts.museum')
@section('title', 'Collection | Museum of Failed Projects')
@section('content')
<section class="shell catalogue">
    <header class="catalogue-head">
        <div><p class="eyebrow"><span class="rule-mark" aria-hidden="true"></span> Museum catalogue</p>
            <h1 class="display-title">The <em>collection.</em></h1>
            <p class="lede">Browse the unfinished work, read what happened, and see what might come next.</p></div>
        <a class="primary-link" href="{{ auth()->check() ? route('my.projects.create') : route('register') }}">Submit a project</a>
    </header>
    <nav class="catalogue-filters" aria-label="Filter by category">
        <a href="{{ route('projects.index', ['adoption' => $openOnly ? 'open' : null]) }}" @if(!$selectedCategory) aria-current="true" @endif>All categories</a>
        @foreach ($categories as $category)
            <a href="{{ route('projects.index', ['category' => $category->slug, 'adoption' => $openOnly ? 'open' : null]) }}" @if($selectedCategory?->is($category)) aria-current="true" @endif>{{ $category->name }}</a>
        @endforeach
    </nav>
    <div class="results-bar">
        <p>{{ $projects->total() }} {{ Str::plural('exhibit', $projects->total()) }}</p>
        <a class="filter-toggle" href="{{ route('projects.index', ['category' => $selectedCategory?->slug, 'adoption' => $openOnly ? null : 'open']) }}">
            <span aria-hidden="true">{{ $openOnly ? '☑' : '□' }}</span> {{ $openOnly ? 'Show all availability' : 'Open for adoption only' }}</a>
    </div>
    @if ($projects->isEmpty())
        <p class="empty-note">No published exhibits match these filters. <a class="text-link" href="{{ route('projects.index') }}">Browse all exhibits</a></p>
    @else
        <x-exhibit-list :projects="$projects" />
        <div class="pagination-wrap">{{ $projects->links() }}</div>
    @endif
</section>
@endsection
