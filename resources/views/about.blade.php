@extends('layouts.museum')
@section('title', 'About the museum | Museum of Failed Projects')
@section('content')
<article class="mx-auto max-w-3xl space-y-8 px-6 py-16">
    <header>
        <h1 class="text-4xl font-bold">About the museum</h1>
        <p class="mt-4 text-2xl text-amber-800">A home for ideas that never made it.</p>
    </header>
    <p class="text-lg leading-8">A game with one finished level. A novel with a missing ending. A prototype that worked exactly once. Each has a place here. Creators share what they hoped to make, where it went wrong, and what someone else might try.</p>
    <section>
        <h2 class="text-xl font-bold">How adoption works</h2>
        <p class="mt-3 leading-7">When a creator opens an exhibit for adoption, you can write to them with your plan. If they approve, you get a private draft linked to the original. Publish it when you’re ready and share progress as you go. The original exhibit stays with its creator.</p>
    </section>
    <a class="inline-block rounded-lg bg-zinc-900 px-5 py-3 font-semibold text-white" href="{{ route('projects.index') }}">Explore the museum</a>
</article>
@endsection
