@props(['title'])

{{-- Clients area: Blade renders the page, Angular custom elements add the interactive parts. --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} · SoftNeos</title>

    @fonts
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
    <script type="module" src="{{ asset('angular/main.js') }}"></script>
</head>
<body class="d-flex flex-column min-vh-100 bg-light">
    <a class="visually-hidden-focusable position-absolute top-0 start-0 m-2 btn btn-accent" href="#contingut">Salta al contingut</a>

    <header class="site-header sticky-top">
        <nav class="navbar navbar-expand-md" aria-label="Àrea de clients">
            <div class="container">
                <a class="navbar-brand brand-logo" href="{{ route('home') }}">Soft<span>Neos</span></a>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu-area"
                        aria-controls="menu-area" aria-expanded="false" aria-label="Obre el menú">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="menu-area">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a @class(['nav-link', 'active' => request()->routeIs('quotes.*')])
                               href="{{ route('quotes.index') }}"
                               @if (request()->routeIs('quotes.*')) aria-current="page" @endif>Pressupostos</a>
                        </li>
                        <li class="nav-item">
                            <a @class(['nav-link', 'active' => request()->routeIs('customers.*')])
                               href="{{ route('customers.index') }}"
                               @if (request()->routeIs('customers.*')) aria-current="page" @endif>Clients</a>
                        </li>
                        <li class="nav-item">
                            <a @class(['nav-link', 'active' => request()->routeIs('references.*')])
                               href="{{ route('references.index') }}"
                               @if (request()->routeIs('references.*')) aria-current="page" @endif>Referències</a>
                        </li>
                    </ul>
                    <div class="d-flex align-items-center gap-3">
                        <span class="small text-secondary">{{ auth()->user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-primary btn-sm">Surt</button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <main id="contingut" class="flex-grow-1 py-4 py-lg-5">
        <div class="container">
            {{ $slot }}
        </div>
    </main>
</body>
</html>
