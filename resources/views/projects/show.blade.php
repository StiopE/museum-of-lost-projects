@extends('layouts.museum')
@section('title', $project->title.' | Museum of Failed Projects')
@section('content')
<article class="shell exhibit">
    <a class="text-link" href="{{ route('projects.index') }}">Back to the collection</a>
    <header class="exhibit-heading">
        <p class="eyebrow">Exhibit MFP / {{ str_pad((string) $project->id, 3, '0', STR_PAD_LEFT) }}</p>
        <h1>{{ $project->title }}</h1>
        <p class="lede">{{ $project->description }}</p>
    </header>
    <div class="exhibit-grid">
        <dl class="exhibit-facts">
            <div><dt>Creator</dt><dd>{{ $project->creator->name }}</dd></div>
            <div><dt>Collection</dt><dd><a class="text-link" href="{{ route('projects.index', ['category' => $project->category->slug]) }}">{{ $project->category->name }}</a></dd></div>
            <div><dt>Adoption</dt><dd>{{ $project->adoption_available ? 'Open for adoption' : 'Closed' }}</dd></div>
        </dl>
        <div>
            @foreach ($project->images as $image)
                <figure class="exhibit-art">
                    <img src="{{ route('projects.images.show', [$project, $image]) }}" alt="{{ $image->caption ?: $project->title }}" loading="lazy">
                    @if ($image->caption)<figcaption>{{ $image->caption }}</figcaption>@endif
                </figure>
            @endforeach
            @foreach (['story' => 'The story', 'original_goal' => 'The original goal', 'abandonment_reason' => 'Why it stopped', 'what_went_wrong' => 'What went wrong', 'what_could_be_improved' => 'What could be improved'] as $field => $heading)
                <section class="story-segment">
                    <h2>{{ $heading }}</h2>
                    <p>{{ $project->{$field} }}</p>
                </section>
            @endforeach
            <section class="journal">
                <h2>Progress updates</h2>
                @forelse ($updates as $update)
                    <article>
                        <header>{{ $update->author->name }} · <time datetime="{{ $update->created_at->toDateString() }}">{{ $update->created_at->format('F j, Y') }}</time></header>
                        <p>{{ $update->content }}</p>
                    </article>
                @empty
                    <p class="mt-3">No progress updates have been shared yet.</p>
                @endforelse
                @if ($updates->hasPages())
                    <div class="pagination-wrap">{{ $updates->links() }}</div>
                @endif
            </section>
            @if (($project->adoption_available || $adoptionRequest) && auth()->id() !== $project->user_id)
                <section class="exhibit-action">
                    <h2 class="text-xl font-bold">Give this idea a second life</h2>
                    @guest
                        <p class="mt-2">Log in to ask the creator about reviving this project.</p>
                        <a class="mt-4 inline-block font-semibold underline" href="{{ route('login') }}">Log in</a>
                    @else
                        @if ($adoptionRequest)
                            <p class="mt-2">Your request is {{ $adoptionRequest->status->value }}.</p>
                            @if ($adoptionRequest->status->value === 'pending')
                                <form class="mt-4" method="POST" action="{{ route('my.adoption-requests.cancel', $adoptionRequest) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="font-semibold underline">Cancel request</button>
                                </form>
                            @elseif ($adoptionRequest->revived_project_id)
                                <a class="mt-4 inline-block font-semibold underline" href="{{ route('my.projects.edit', $adoptionRequest->revived_project_id) }}">Manage revived project</a>
                            @endif
                        @endif
                        @if ($project->adoption_available && !in_array($adoptionRequest?->status->value, ['pending', 'approved'], true))
                            <form class="mt-4 space-y-3" method="POST" action="{{ route('projects.adoption-requests.store', $project) }}">
                                @csrf
                                <label class="block text-sm font-medium" for="message">Tell the creator what you would do with it</label>
                                <textarea class="w-full rounded-lg border-stone-300" id="message" name="message" rows="4" required>{{ old('message') }}</textarea>
                                @error('message')<p class="text-sm text-red-700">{{ $message }}</p>@enderror
                                <button class="rounded-lg bg-zinc-900 px-4 py-2 text-white">Send adoption request</button>
                            </form>
                        @endif
                    @endguest
                </section>
            @endif
            @if ($project->revivedProjects->isNotEmpty())
                <section class="journal mt-8">
                    <h2>Inspired by this project</h2>
                    <ul class="mt-4 space-y-3">
                        @foreach ($project->revivedProjects as $revived)
                            <li><a class="text-link" href="{{ route('projects.show', $revived) }}">{{ $revived->title }}</a> by {{ $revived->creator->name }}</li>
                        @endforeach
                    </ul>
                </section>
            @endif
        </div>
    </div>
</article>
@endsection
