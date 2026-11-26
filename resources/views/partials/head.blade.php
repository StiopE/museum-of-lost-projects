<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>
    {{ filled($title ?? null) ? $title.' | Museum of Failed Projects' : 'Museum of Failed Projects' }}
</title>

<link rel="icon" href="{{ asset('museum-mark.svg') }}" type="image/svg+xml">

@fonts

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
