@extends('layouts.museum')
@section('title', 'Adoption requests | Museum of Failed Projects')
@section('content')
<section class="mx-auto max-w-4xl space-y-8 px-6 py-12">
    <h1 class="text-4xl font-bold">Adoption requests</h1>
    <section class="rounded-2xl border bg-white p-6">
        <h2 class="text-xl font-bold">Received on your projects</h2>
        @forelse ($received as $request)
            <article class="mt-4 border-t pt-4">
                <h3 class="font-semibold">{{ $request->project->title }} · {{ ucfirst($request->status->value) }}</h3>
                <p>From {{ $request->requester->name }}</p>
                <p class="mt-2 whitespace-pre-line">{{ $request->message }}</p>
                <a class="mt-2 inline-block font-semibold text-amber-800" href="{{ route('my.projects.edit', $request->project) }}#request-{{ $request->id }}">Review request →</a>
            </article>
        @empty
            <p class="mt-3 text-zinc-600">Nobody has requested one of your projects yet.</p>
        @endforelse
        {{ $received->links() }}
    </section>
    <section class="rounded-2xl border bg-white p-6">
        <h2 class="text-xl font-bold">Sent by you</h2>
        @forelse ($sent as $request)
            <article class="mt-4 border-t pt-4">
                <h3 class="font-semibold">
                    @if ($request->project->status->value === 'published')
                        <a href="{{ route('projects.show', $request->project) }}" class="text-amber-800">{{ $request->project->title }}</a>
                    @else
                        Archived project
                    @endif
                    · {{ ucfirst($request->status->value) }}
                </h3>
                <p class="mt-2 whitespace-pre-line">{{ $request->message }}</p>
                @if ($request->revived_project_id)
                    <a class="mt-2 inline-block font-semibold text-amber-800" href="{{ route('my.projects.edit', $request->revived_project_id) }}">Manage revived project →</a>
                @elseif ($request->status->value === 'pending')
                    <form method="POST" action="{{ route('my.adoption-requests.cancel', $request) }}" class="mt-2">
                        @csrf
                        @method('PATCH')
                        <button class="font-semibold underline">Cancel request</button>
                    </form>
                @endif
            </article>
        @empty
            <p class="mt-3 text-zinc-600">You haven’t requested a project yet.</p>
        @endforelse
        {{ $sent->links() }}
    </section>
</section>
@endsection
