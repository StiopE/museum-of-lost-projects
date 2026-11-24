@extends('layouts.museum')

@section('content')
    <section class="mx-auto max-w-6xl px-6 py-24 sm:py-32">
        <p class="font-semibold uppercase tracking-[0.2em] text-amber-700">The Museum of Failed Projects</p>
        <h1 class="mt-5 max-w-3xl text-5xl font-bold tracking-tight text-zinc-900 sm:text-7xl">Good ideas deserve a second life.</h1>
        <p class="mt-6 max-w-2xl text-lg leading-8 text-zinc-600">Discover projects that were left unfinished, learn what happened, and find ideas worth bringing back.</p>
        <a href="{{ route('projects.index') }}" class="mt-9 inline-flex rounded-lg bg-zinc-900 px-5 py-3 font-semibold text-white hover:bg-zinc-700">Explore the museum</a>
    </section>
@endsection
