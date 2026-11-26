@extends('layouts.museum')
@section('content')
<section class="shell catalogue">
    <a class="text-link" href="{{ route('admin.categories.index') }}">All categories</a>
    <h1 class="display-title">{{ $category->exists ? 'Edit category' : 'Add category' }}</h1>
    <form class="my-6 grid max-w-xl gap-4" method="POST" action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}">
        @csrf
        @if ($category->exists) @method('PUT') @endif
        <label>Name <input class="block w-full border p-2" name="name" required value="{{ old('name', $category->name) }}"></label>
        <label>URL slug <input class="block w-full border p-2" name="slug" required placeholder="art-design" value="{{ old('slug', $category->slug) }}"></label>
        <label>Description <textarea class="block w-full border p-2" name="description">{{ old('description', $category->description) }}</textarea></label>
        @foreach ($errors->all() as $message)<p role="alert">{{ $message }}</p>@endforeach
        <button class="primary-link">Save category</button>
    </form>
</section>
@endsection
