@extends('layouts.museum')
@section('content')
<section class="shell catalogue">
    <h1 class="display-title">Curator review</h1>
    @forelse ($projects as $project)
        <article class="story-segment">
            <h2><a class="text-link" href="{{ route('my.projects.edit', $project) }}">{{ $project->title }}</a></h2>
            <p>{{ $project->creator->name }} · {{ $project->description }}</p>
            <form class="mt-4 flex gap-5" method="POST" action="{{ route('admin.reviews.decide', $project) }}">@csrf @method('PATCH')
                <button class="primary-link" name="decision" value="publish">Publish exhibit</button>
                <button class="text-link" name="decision" value="return">Return to draft</button>
            </form>
        </article>
    @empty
        <p class="empty-note">No submissions are waiting for review.</p>
    @endforelse
    {{ $projects->links() }}
    <h2 class="mt-8 text-3xl">Pending adoption requests</h2>
    @forelse ($requests as $request)
        <p class="mt-3"><a class="text-link" href="{{ route('my.projects.edit', $request->project) }}#request-{{ $request->id }}">{{ $request->project->title }} · {{ $request->requester->name }}</a></p>
    @empty
        <p>No pending adoption requests.</p>
    @endforelse
    {{ $requests->links() }}
</section>
@endsection
