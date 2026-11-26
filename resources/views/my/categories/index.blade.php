@extends('layouts.museum')
@section('content')
<section class="shell catalogue">
    <h1 class="display-title">Categories</h1>
    <a class="primary-link my-6" href="{{ route('admin.categories.create') }}">Add category</a>
    @foreach ($categories as $category)
        <article class="flex items-center gap-6 border-t py-4">
            <a class="text-link" href="{{ route('admin.categories.edit', $category) }}">{{ $category->name }}</a>
            <span>{{ $category->projects_count }} projects</span>
            @if (!$category->projects_count)
                <form method="POST" action="{{ route('admin.categories.destroy', $category) }}">@csrf @method('DELETE')<button class="text-link">Delete</button></form>
            @endif
        </article>
    @endforeach
    {{ $categories->links() }}
</section>
@endsection
