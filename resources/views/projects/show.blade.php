@extends('layouts.museum')
@section('title', $project->title.' | Museum of Failed Projects')
@section('content')
<article class="mx-auto max-w-4xl px-6 py-12">
    <a href="{{ route('projects.index') }}" class="text-sm font-medium text-amber-800">← Back to the collection</a>
    <div class="mt-8"><p class="font-semibold text-amber-700">{{ $project->category->name }}</p><h1 class="mt-2 text-4xl font-bold sm:text-5xl">{{ $project->title }}</h1><p class="mt-5 text-xl leading-8 text-zinc-600">{{ $project->description }}</p><p class="mt-4 text-sm text-zinc-500">Shared by {{ $project->creator->name }}</p></div>
    @if ($project->images->isNotEmpty())
        <div class="mt-8 grid gap-4 sm:grid-cols-2">
            @foreach ($project->images as $image)
                <img src="{{ route('projects.images.show', [$project, $image]) }}" alt="{{ $image->caption ?: $project->title }}" class="max-h-96 w-full rounded-2xl object-cover" loading="lazy">
            @endforeach
        </div>
    @endif
    <div class="mt-10 space-y-8">
        @foreach (['story' => 'The story', 'original_goal' => 'The original goal', 'abandonment_reason' => 'Why it stopped', 'what_went_wrong' => 'What went wrong', 'what_could_be_improved' => 'What could be improved'] as $field => $heading)
            <section class="rounded-2xl border border-stone-200 bg-white p-6"><h2 class="text-xl font-semibold">{{ $heading }}</h2><p class="mt-3 whitespace-pre-line leading-7 text-zinc-700">{{ $project->{$field} }}</p></section>
        @endforeach
    </div>
    <section class="mt-12">
        <h2 class="text-2xl font-bold">Progress updates</h2>
        @forelse ($updates as $update)
            <article class="mt-4 rounded-2xl border border-stone-200 bg-white p-6">
                <p class="text-sm text-zinc-500">{{ $update->author->name }} · {{ $update->created_at->format('F j, Y') }}</p>
                <p class="mt-3 whitespace-pre-line leading-7 text-zinc-700">{{ $update->content }}</p>
            </article>
        @empty
            <p class="mt-3 text-zinc-600">No progress updates have been shared yet.</p>
        @endforelse
        @if ($updates->hasPages())
            <div class="mt-6">{{ $updates->links() }}</div>
        @endif
    </section>
    @if ($project->adoption_available && auth()->id() !== $project->user_id)
        <section class="mt-8 rounded-2xl bg-amber-100 p-6"><h2 class="text-xl font-bold">Give this idea a second life</h2>
            @guest<p class="mt-2">Log in to ask the creator about reviving this project.</p><a class="mt-4 inline-block font-semibold underline" href="{{ route('login') }}">Log in</a>@else
                @if ($adoptionRequest?->status->value === 'pending')
                    <p class="mt-2">Your request is waiting for the creator.</p><form class="mt-4" method="POST" action="{{ route('my.adoption-requests.cancel', $adoptionRequest) }}">@csrf @method('PATCH')<button class="font-semibold underline">Cancel request</button></form>
                @elseif (!$adoptionRequest || $adoptionRequest->status->value !== 'approved')
                    <form class="mt-4 space-y-3" method="POST" action="{{ route('projects.adoption-requests.store', $project) }}">@csrf<label class="block text-sm font-medium" for="message">Tell the creator what you would do with it</label><textarea class="w-full rounded-lg border-stone-300" id="message" name="message" rows="4" required>{{ old('message') }}</textarea>@error('message')<p class="text-sm text-red-700">{{ $message }}</p>@enderror<button class="rounded-lg bg-zinc-900 px-4 py-2 text-white">Send adoption request</button></form>
                @else<p class="mt-2">Your request was approved.</p>@endif
            @endguest
        </section>
    @endif
    @if ($project->revivedProjects->isNotEmpty())
        <section class="mt-10"><h2 class="text-2xl font-bold">Inspired by this project</h2><div class="mt-4 space-y-3">@foreach ($project->revivedProjects as $revived)<a class="block rounded-xl border bg-white p-4 font-semibold" href="{{ route('projects.show', $revived) }}">{{ $revived->title }} <span class="text-sm font-normal text-zinc-500">by {{ $revived->creator->name }}</span></a>@endforeach</div></section>
    @endif
</article>
@endsection
