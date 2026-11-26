<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f7f6ef">
    <meta name="description" content="@yield('description', 'Discover unfinished projects, the people who made them, and ideas waiting for a second life.')">
    <title>@yield('title', 'Museum of Failed Projects')</title>
    <link rel="icon" href="{{ asset('museum-mark.svg') }}" type="image/svg+xml">
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="museum">
    <a class="skip-link" href="#main">Skip to content</a>
    <header class="masthead">
        <div class="shell masthead-inner">
            <x-museum-brand />
            <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="museum-nav" hidden>
                <span>Menu</span><span class="menu-lines" aria-hidden="true"></span>
            </button>
            <nav id="museum-nav" class="museum-nav" aria-label="Main navigation">
                <a href="{{ route('projects.index') }}" @if(request()->routeIs('projects.index')) aria-current="page" @endif>Collection</a>
                <a href="{{ route('about') }}" @if(request()->routeIs('about')) aria-current="page" @endif>About</a>
                @auth
                    <a href="{{ route('dashboard') }}" @if(request()->routeIs('dashboard')) aria-current="page" @endif>Dashboard</a>
                    <a href="{{ route('my.projects.index') }}" @if(request()->routeIs('my.projects.*')) aria-current="page" @endif>My Projects</a>
                    <a href="{{ route('profile.edit') }}">Account settings</a>
                    <form method="POST" action="{{ route('logout') }}">@csrf<button class="nav-action" type="submit">Log out</button></form>
                @else
                    <a href="{{ route('login') }}" class="nav-login">Log in</a>
                    <a href="{{ route('register') }}" class="nav-action">Join the museum</a>
                @endauth
            </nav>
        </div>
    </header>
    @if (session('status'))
        <div class="shell notice" role="status">{{ session('status') }}</div>
    @endif
    <main id="main" tabindex="-1" @class(['workspace' => request()->routeIs('dashboard', 'my.*')])>@yield('content')</main>
    <footer class="museum-footer">
        <div class="shell footer-inner">
            <x-museum-brand />
            <p>A home for ideas that never made it.</p>
            <a href="{{ route('about') }}">About the museum</a>
        </div>
    </footer>
</body>
</html>
