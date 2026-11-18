@props(['title' => null, 'description' => 'SoftNeos Suite: programari de gestió per a la petita i mitjana indústria.'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $description }}">
    <title>{{ $title ? $title.' · SoftNeos' : 'SoftNeos · Programari de gestió industrial' }}</title>

    @fonts
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>
<body class="d-flex flex-column min-vh-100">
    <a class="visually-hidden-focusable position-absolute top-0 start-0 m-2 btn btn-accent" href="#contingut">Salta al contingut</a>

    <x-navbar />

    <main id="contingut" class="flex-grow-1">
        {{ $slot }}
    </main>

    <x-footer />
</body>
</html>
