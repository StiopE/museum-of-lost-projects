@extends('layouts.museum')
@section('title', 'Submit a project')
@section('content')
<section class="mx-auto max-w-3xl px-6 py-12"><a href="{{ route('my.projects.index') }}" class="text-sm text-amber-800">← My Projects</a><h1 class="mt-5 text-4xl font-bold">Share an unfinished project</h1><p class="mt-3 mb-8 text-zinc-600">Tell the story honestly. Your project will stay private as a draft until you publish it.</p><div class="rounded-2xl border bg-white p-6 sm:p-8">@include('my.projects._form')</div></section>
@endsection
