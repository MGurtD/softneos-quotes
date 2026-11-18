@php
    $sections = [
        'qui-som' => 'Qui som',
        'moduls' => 'Mòduls',
        'pressupostos' => 'Pressupostos',
        'contacte' => 'Contacte',
    ];
@endphp

<header class="site-header sticky-top">
    <nav class="navbar navbar-expand-md" aria-label="Navegació principal">
        <div class="container">
            <a class="navbar-brand brand-logo" href="{{ route('home') }}">Soft<span>Neos</span></a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu-principal"
                    aria-controls="menu-principal" aria-expanded="false" aria-label="Obre el menú">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="menu-principal">
                <ul class="navbar-nav ms-auto align-items-md-center gap-md-1">
                    @foreach ($sections as $anchor => $label)
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('home') }}#{{ $anchor }}">{{ $label }}</a>
                        </li>
                    @endforeach
                    <li class="nav-item ms-md-3 mt-2 mt-md-0">
                        <a class="btn btn-accent" href="{{ url('/login') }}">Accés clients</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>
