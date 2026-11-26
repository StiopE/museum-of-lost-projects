@extends('layouts.museum')
@section('title', 'About the museum | Museum of Failed Projects')
@section('content')
<article class="shell about">
    <header class="about-heading">
        <p class="eyebrow"><span class="rule-mark" aria-hidden="true"></span> About the museum</p>
        <h1>A home for ideas that <em>never made it.</em></h1>
    </header>
    <div class="about-grid">
        <figure class="exhibit-art">
            <img src="{{ asset('images/museum/paper-study.webp') }}" width="1000" height="750" alt="A worn stack of postcards beside a rubber stamp and pencil.">
            <figcaption>From the Writing &amp; Stories shelf.</figcaption>
        </figure>
        <div>
            <p>A game with one finished level. A novel with a missing ending. A prototype that worked exactly once. Each has a place here. Creators share what they hoped to make, where it went wrong, and what someone else might try.</p>
            <h2>How adoption works</h2>
            <p>When a creator opens an exhibit for adoption, you can write to them with your plan. If they approve, you get a private draft linked to the original. Publish it when you’re ready and share progress as you go. The original exhibit stays with its creator.</p>
            <a class="primary-link" href="{{ route('projects.index') }}">Explore the museum</a>
        </div>
    </div>
</article>
@endsection
