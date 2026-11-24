@php($isEditing = isset($project))
<form method="POST" action="{{ $isEditing ? route('my.projects.update', $project) : route('my.projects.store') }}" class="space-y-5">
    @csrf
    @if ($isEditing) @method('PUT') @endif
    <div><label for="title" class="mb-1 block font-medium">Project title</label><input id="title" name="title" value="{{ old('title', $project->title ?? '') }}" required maxlength="255" class="w-full rounded-lg border-stone-300">@error('title')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror</div>
    <div><label for="category_id" class="mb-1 block font-medium">Category</label><select id="category_id" name="category_id" required class="w-full rounded-lg border-stone-300"><option value="">Choose a category</option>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected(old('category_id', $project->category_id ?? '') == $category->id)>{{ $category->name }}</option>@endforeach</select>@error('category_id')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror</div>
    <div><label for="description" class="mb-1 block font-medium">Short description</label><textarea id="description" name="description" rows="2" maxlength="280" required class="w-full rounded-lg border-stone-300">{{ old('description', $project->description ?? '') }}</textarea>@error('description')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror</div>
    @foreach (['story' => 'What is the story behind it?', 'original_goal' => 'What were you trying to achieve?', 'abandonment_reason' => 'Why did you stop?', 'what_went_wrong' => 'What went wrong?', 'what_could_be_improved' => 'What could be improved?'] as $field => $label)
        <div><label for="{{ $field }}" class="mb-1 block font-medium">{{ $label }}</label><textarea id="{{ $field }}" name="{{ $field }}" rows="4" required class="w-full rounded-lg border-stone-300">{{ old($field, $project->{$field} ?? '') }}</textarea>@error($field)<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror</div>
    @endforeach
    <button class="rounded-lg bg-zinc-900 px-5 py-3 font-semibold text-white">{{ $isEditing ? 'Save changes' : 'Save as draft' }}</button>
</form>
