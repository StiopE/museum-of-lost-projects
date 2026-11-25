<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Museum of Failed Projects')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-stone-50 text-zinc-900">
    <header class="border-b border-stone-200 bg-white">
        <nav class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-4 px-6 py-4">
            <a href="{{ route('home') }}" class="font-bold">Museum of Failed Projects</a>
            <div class="flex flex-wrap items-center gap-x-5 gap-y-3 text-sm font-medium">
                <a href="{{ route('projects.index') }}" class="hover:text-amber-700">Explore</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="hover:text-amber-700">Dashboard</a>
                    <a href="{{ route('my.projects.index') }}" class="hover:text-amber-700">My Projects</a>
                    <a href="{{ route('profile.edit') }}" class="hover:text-amber-700">Account settings</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="hover:text-amber-700">Log out</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="hover:text-amber-700">Log in</a>
                    <a href="{{ route('register') }}" class="rounded-lg bg-zinc-900 px-4 py-2 text-white hover:bg-zinc-700">Join</a>
                @endauth
            </div>
        </nav>
    </header>
    @if (session('status'))
        <div class="mx-auto mt-6 max-w-6xl rounded-lg bg-green-100 px-4 py-3 text-green-900">{{ session('status') }}</div>
    @endif
    <main>@yield('content')</main>
</body>
</html>
