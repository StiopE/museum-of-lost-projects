@extends('layouts.museum')
@section('content')
<section class="shell opening">
    <div class="opening-copy">
        <p class="eyebrow"><span class="rule-mark" aria-hidden="true"></span> A digital museum</p>
        <h1 class="opening-title"><span>A home for</span> <em>unfinished</em> <span>ideas.</span></h1>
        <p class="opening-intro">Abandoned prototypes, half-written stories, and the people who left them behind. Find an idea worth picking up.</p>
        <a class="primary-link" href="{{ route('projects.index') }}">Explore the collection</a>
    </div>
    <figure class="opening-figure">
        <div class="press-frame">
            <img src="{{ asset('images/museum/hero-press.webp') }}"
                srcset="{{ asset('images/museum/hero-press-small.webp') }} 720w, {{ asset('images/museum/hero-press.webp') }} 1400w"
                sizes="(max-width: 700px) 92vw, 52vw" width="1400" height="1050" fetchpriority="high"
                alt="A handmade rain gauge with a silver funnel, exposed wiring and a curling paper receipt.">
        </div>
        <figcaption>
            <span>Rain Ticket Press</span>
            @if ($pressExhibit)
                <a class="text-link" href="{{ route('projects.show', $pressExhibit) }}" aria-label="View the Rain Ticket Press exhibit">View exhibit <span aria-hidden="true">↗</span></a>
            @else
                <span>Prototype study</span>
            @endif
        </figcaption>
    </figure>
</section>

<section class="collection-section">
    <div class="shell">
        <header class="section-heading">
            <div><p class="eyebrow"><span class="rule-mark" aria-hidden="true"></span> Collection index</p>
                <h2 class="display-title">On the <em>shelves.</em></h2></div>
            <a class="text-link" href="{{ route('projects.index') }}">View all {{ $exhibitCount }} {{ Str::plural('exhibit', $exhibitCount) }}</a>
        </header>
        @php
            $shelves = [
                'art-design' => ['image' => 'loom-study.webp', 'alt' => 'Indigo and rust threads on a hand-cut cardboard loom.'],
                'writing-stories' => ['image' => 'paper-study.webp', 'alt' => 'A stack of coloured postcards with a stamp and a sharpened pencil.'],
            ];
        @endphp
        <div class="shelf-pair">
            @foreach ($shelves as $slug => $shelf)
                @if ($category = $categories->get($slug))
                    <a class="shelf" href="{{ route('projects.index', ['category' => $category->slug]) }}">
                        <div class="shelf-photo"><img data-reveal loading="lazy" width="1000" height="750" src="{{ asset('images/museum/'.$shelf['image']) }}" alt="{{ $shelf['alt'] }}"></div>
                        <div class="shelf-caption"><h3>{{ $category->name }}</h3><span>{{ $category->exhibit_count }} {{ Str::plural('exhibit', $category->exhibit_count) }}</span></div>
                    </a>
                @endif
            @endforeach
        </div>
        @if ($recentProjects->isNotEmpty())
            <h3 class="index-label">Recently added</h3>
            <x-exhibit-list :projects="$recentProjects" />
        @else
            <p class="empty-note">The shelves are ready for the first exhibit.</p>
        @endif
    </div>
</section>

<section class="adoption-band"><div class="shell adoption-inner">
    <div><p class="eyebrow">Project adoption</p><h2>Pick up where<br>someone <em>left off.</em></h2></div>
    <div><p>Tell the creator what you’d try next. With their approval, you can start a revival that stays linked to the original.</p>
        <a class="light-link" href="{{ route('projects.index', ['adoption' => 'open']) }}">Find an idea to revive</a></div>
</div></section>
@endsection
