@extends('layouts.museum')
@section('title', 'Manage '.$project->title)
@section('content')
<section class="mx-auto max-w-3xl px-6 py-12"><a href="{{ route('my.projects.index') }}" class="text-sm text-amber-800">← My Projects</a><div class="mt-5 flex flex-wrap items-center justify-between gap-3"><div><p class="font-semibold uppercase tracking-widest text-amber-700">{{ $project->status->value }}</p><h1 class="mt-2 text-4xl font-bold">Manage project</h1></div>@if ($project->status->value === 'published')<a class="font-medium text-amber-800" href="{{ route('projects.show', $project) }}">View public page →</a>@endif</div>
    <div class="mt-8 rounded-2xl border bg-white p-6 sm:p-8"><h2 class="mb-6 text-xl font-bold">Project details</h2>@include('my.projects._form', ['project' => $project])</div>
    <section class="mt-6 rounded-2xl border bg-white p-6 sm:p-8">
        <h2 class="text-xl font-bold">Project images <span class="text-sm font-normal text-zinc-500">({{ $project->images->count() }} of 6)</span></h2>
        @if ($project->images->isNotEmpty())
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                @foreach ($project->images as $image)
                    <div class="overflow-hidden rounded-xl border">
                        <img src="{{ route('projects.images.show', [$project, $image]) }}" alt="{{ $image->caption ?: $project->title }}" class="h-48 w-full object-cover">
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
    @if ($project->status->value !== 'published')
        <form method="POST" action="{{ route('my.projects.publish', $project) }}" class="mt-6 rounded-2xl border bg-white p-6">@csrf @method('PATCH')<h2 class="text-xl font-bold">Publish this project</h2><p class="mt-2 text-sm text-zinc-600">Publishing makes the story visible in the public museum.</p><label class="mt-4 flex items-center gap-3"><input type="checkbox" name="adoption_available" value="1" class="rounded border-stone-300">Allow people to request adopting this idea</label><button class="mt-5 rounded-lg bg-amber-700 px-4 py-2 font-semibold text-white">Publish project</button></form>
    @elseif ($project->adoptionRequests->isNotEmpty())
        <section class="mt-6 rounded-2xl border bg-white p-6"><h2 class="text-xl font-bold">Adoption requests</h2><div class="mt-4 space-y-4">@foreach ($project->adoptionRequests as $request)<article class="rounded-xl bg-stone-50 p-4"><div class="flex flex-wrap justify-between gap-2"><p class="font-semibold">{{ $request->requester->name }}</p><span class="text-sm capitalize text-zinc-500">{{ $request->status->value }}</span></div><p class="mt-2 whitespace-pre-line text-zinc-700">{{ $request->message }}</p>@if ($request->status->value === 'pending')<div class="mt-4 flex gap-3"><form method="POST" action="{{ route('my.adoption-requests.approve', $request) }}">@csrf @method('PATCH')<button class="rounded-lg bg-green-700 px-4 py-2 text-sm font-semibold text-white">Approve</button></form><form method="POST" action="{{ route('my.adoption-requests.reject', $request) }}">@csrf @method('PATCH')<button class="rounded-lg border px-4 py-2 text-sm font-semibold">Reject</button></form></div>@endif</article>@endforeach</div></section>
    @endif
    @if ($project->status->value !== 'archived')<form method="POST" action="{{ route('my.projects.archive', $project) }}" class="mt-6 rounded-2xl border bg-white p-6">@csrf @method('PATCH')<h2 class="text-lg font-bold">Archive project</h2><p class="mt-1 text-sm text-zinc-600">Remove it from public browsing while keeping it in your workspace.</p><button class="mt-4 rounded-lg border px-4 py-2 font-medium">Archive</button></form>@endif
    @can('delete', $project)<form method="POST" action="{{ route('my.projects.destroy', $project) }}" class="mt-6 rounded-2xl border border-red-200 bg-white p-6">@csrf @method('DELETE')<h2 class="text-lg font-bold text-red-800">Delete project</h2><p class="mt-1 text-sm text-zinc-600">Permanently remove this project and its related records.</p><button class="mt-4 rounded-lg bg-red-700 px-4 py-2 font-medium text-white" onclick="return confirm('Permanently delete this project?')">Delete project</button></form>@endcan
</section>
@endsection
