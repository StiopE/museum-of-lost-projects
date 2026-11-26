@extends('layouts.museum')
@section('title', 'Manage '.$project->title)
@section('content')
<section class="mx-auto max-w-3xl px-6 py-12"><a href="{{ route('my.projects.index') }}" class="text-sm text-amber-800">← My Projects</a><div class="mt-5 flex flex-wrap items-center justify-between gap-3"><div><p class="font-semibold uppercase tracking-widest text-amber-700">{{ $project->status->value }}</p><h1 class="mt-2 text-4xl font-bold">Manage project</h1></div>@if ($project->status->value === 'published')<a class="font-medium text-amber-800" href="{{ route('projects.show', $project) }}">View public page →</a>@endif</div>
    @if ($project->parentProject?->status === \App\Enums\ProjectStatus::Published)
        <p class="mt-4">Revival of <a class="text-link" href="{{ route('projects.show', $project->parentProject) }}">{{ $project->parentProject->title }}</a></p>
    @endif
    <div class="mt-8 rounded-2xl border bg-white p-6 sm:p-8"><h2 class="mb-6 text-xl font-bold">Project details</h2>@include('my.projects._form', ['project' => $project])</div>
    <section class="mt-6 rounded-2xl border bg-white p-6 sm:p-8">
        <h2 class="text-xl font-bold">Project images <span class="text-sm font-normal text-zinc-500">({{ $project->images->count() }} of 6)</span></h2>
        @if ($project->images->isNotEmpty())
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                @foreach ($project->images as $image)
                    <div class="overflow-hidden rounded-xl border">
                        <img src="{{ route('projects.images.show', [$project, $image]) }}" alt="{{ $image->caption ?: $project->title }}" class="h-48 w-full object-cover">
                        <form method="POST" action="{{ route('my.projects.images.update', [$project, $image]) }}" class="grid gap-2 p-3">@csrf @method('PUT')
                            <label>Caption <input class="w-full" name="caption" maxlength="255" value="{{ $image->caption }}"></label>
                            <label>Display order (lowest first) <input type="number" name="sort_order" min="0" max="999" required value="{{ $image->sort_order }}"></label>
                            @foreach ($errors->getBag('image'.$image->id)->all() as $message)<p role="alert">{{ $message }}</p>@endforeach
                            <button class="text-link">Save image details</button>
                        </form>
                        <form method="POST" action="{{ route('my.projects.images.destroy', [$project, $image]) }}" class="p-3">@csrf @method('DELETE')<button class="text-sm font-semibold text-red-700">Remove image</button></form>
                    </div>
                @endforeach
            </div>
        @endif
        @if ($project->images->count() < 6)
            <form method="POST" action="{{ route('my.projects.images.store', $project) }}" enctype="multipart/form-data" class="mt-6 space-y-4">@csrf
                <div><label for="images" class="mb-1 block font-medium">Add images</label><input id="images" name="images[]" type="file" accept="image/jpeg,image/png,image/webp" multiple required class="block w-full text-sm file:mr-4 file:rounded-lg file:border-0 file:bg-stone-100 file:px-4 file:py-2"> <p class="mt-2 text-sm text-zinc-500">JPEG, PNG, or WebP; up to 5 MB each. Maximum six images per project.</p>
                    @foreach ($errors->all() as $error)<p class="mt-1 text-sm text-red-700">{{ $error }}</p>@endforeach
                </div>
                <button class="rounded-lg bg-zinc-900 px-4 py-2 font-semibold text-white">Upload images</button>
            </form>
        @else
            <p class="mt-4 text-sm text-zinc-600">This project has reached the six image limit.</p>
        @endif
    </section>
    <section class="mt-6 rounded-2xl border bg-white p-6 sm:p-8">
        <h2 class="text-xl font-bold">Progress updates</h2>
        <p class="mt-2 text-sm text-zinc-600">Share milestones and what you have learned as you continue the project.</p>
        <form method="POST" action="{{ route('my.projects.updates.store', $project) }}" class="mt-5 space-y-3">@csrf
            <label for="new-update" class="block font-medium">New update</label>
            <textarea id="new-update" name="content" rows="4" maxlength="10000" required class="w-full rounded-lg border-stone-300" placeholder="What changed since your last update?">{{ old('content') }}</textarea>
            @error('content')<p class="text-sm text-red-700">{{ $message }}</p>@enderror
            <button class="rounded-lg bg-zinc-900 px-4 py-2 font-semibold text-white">Share update</button>
        </form>
        <div class="mt-8 space-y-5">
            @forelse ($updates as $update)
                <article class="rounded-xl bg-stone-50 p-4">
                    <p class="mb-3 text-sm text-zinc-500">{{ $update->created_at->format('F j, Y') }}</p>
                    <form method="POST" action="{{ route('my.projects.updates.update', [$project, $update]) }}" class="space-y-3">@csrf @method('PUT')
                        <label class="sr-only" for="update-{{ $update->id }}">Edit progress update</label>
                        <textarea id="update-{{ $update->id }}" name="updates[{{ $update->id }}][content]" rows="4" maxlength="10000" required class="w-full rounded-lg border-stone-300">{{ old('updates.'.$update->id.'.content', $update->content) }}</textarea>
                        @error('updates.'.$update->id.'.content')<p class="text-sm text-red-700">{{ $message }}</p>@enderror
                        <button class="rounded-lg border px-4 py-2 text-sm font-semibold">Save update</button>
                    </form>
                    <form method="POST" action="{{ route('my.projects.updates.destroy', [$project, $update]) }}" class="mt-3">@csrf @method('DELETE')<button class="text-sm font-semibold text-red-700">Delete update</button></form>
                </article>
            @empty
                <p class="text-sm text-zinc-600">You have not shared any progress updates yet.</p>
            @endforelse
        </div>
        @if ($updates->hasPages())
            <div class="mt-6">{{ $updates->links() }}</div>
        @endif
    </section>
    @if ($project->status->value === 'draft')
        <form method="POST" action="{{ route('my.projects.submit', $project) }}" class="mt-6">@csrf @method('PATCH')<button class="primary-link">Submit for curator review</button></form>
    @endif
    @if ($project->status->value === 'submitted')
        <p class="notice">Submitted for curator review.</p>
    @elseif ($project->status->value !== 'published')
        <form method="POST" action="{{ route('my.projects.publish', $project) }}" class="mt-6 rounded-2xl border bg-white p-6">@csrf @method('PATCH')<h2 class="text-xl font-bold">Publish this project</h2><p class="mt-2 text-sm text-zinc-600">Publishing makes the story visible in the public museum.</p><label class="mt-4 flex items-center gap-3"><input type="checkbox" name="adoption_available" value="1" class="rounded border-stone-300">Allow people to request adopting this idea</label><button class="mt-5 rounded-lg bg-amber-700 px-4 py-2 font-semibold text-white">Publish project</button></form>
    @else
        <form method="POST" action="{{ route('my.projects.adoption-availability', $project) }}" class="mt-6 rounded-2xl border bg-white p-6">
            @csrf
            @method('PATCH')
            <h2 class="text-xl font-bold">Adoption availability</h2>
            <p class="mt-2">{{ $project->adoption_available ? 'This project is accepting requests.' : 'This project is closed to new requests.' }}</p>
            <input type="hidden" name="adoption_available" value="{{ $project->adoption_available ? '0' : '1' }}">
            <button class="mt-4 rounded-lg border px-4 py-2 font-semibold">{{ $project->adoption_available ? 'Close to new requests' : 'Open to new requests' }}</button>
        </form>
    @endif
    @error('adoption_available')<p class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
    @if ($project->adoptionRequests->isNotEmpty())
        <section class="mt-6 rounded-2xl border bg-white p-6">
            <h2 class="text-xl font-bold">Adoption requests</h2>
            <div class="mt-4 space-y-4">
                @foreach ($project->adoptionRequests as $request)
                    <article id="request-{{ $request->id }}" class="rounded-xl bg-stone-50 p-4">
                        <p class="font-semibold">{{ $request->requester->name }} · {{ ucfirst($request->status->value) }}</p>
                        <p class="mt-2 whitespace-pre-line text-zinc-700">{{ $request->message }}</p>
                        @if ($request->status->value === 'pending' && auth()->user()->can('approve', $request))
                            <div class="mt-4 flex gap-3">
                                @if ($project->adoption_available)
                                    <form method="POST" action="{{ route('my.adoption-requests.approve', $request) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="rounded-lg bg-green-700 px-4 py-2 text-sm font-semibold text-white">Approve</button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('my.adoption-requests.reject', $request) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="rounded-lg border px-4 py-2 text-sm font-semibold">Reject</button>
                                </form>
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>
        </section>
    @endif
    @if ($project->status->value !== 'archived')<form method="POST" action="{{ route('my.projects.archive', $project) }}" class="mt-6 rounded-2xl border bg-white p-6">@csrf @method('PATCH')<h2 class="text-lg font-bold">Archive project</h2><p class="mt-1 text-sm text-zinc-600">Remove it from public browsing while keeping it in your workspace.</p><button class="mt-4 rounded-lg border px-4 py-2 font-medium">Archive</button></form>@endif
    @can('delete', $project)<form method="POST" action="{{ route('my.projects.destroy', $project) }}" class="mt-6 rounded-2xl border border-red-200 bg-white p-6">@csrf @method('DELETE')<h2 class="text-lg font-bold text-red-800">Delete project</h2><p class="mt-1 text-sm text-zinc-600">Permanently remove this project and its related records.</p><button class="mt-4 rounded-lg bg-red-700 px-4 py-2 font-medium text-white" onclick="return confirm('Permanently delete this project?')">Delete project</button></form>@endcan
</section>
@endsection
