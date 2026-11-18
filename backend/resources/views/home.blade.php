@php
    $newModule = $modules->firstWhere('is_new', true);
@endphp

<x-layouts.public>
    {{-- Hero ------------------------------------------------------------------ --}}
    <section class="hero bg-dark text-white" aria-labelledby="titol">
        <div class="container py-5">
            <div class="row align-items-center g-5 py-lg-5">
                <div class="col-lg-6">
                    <p class="eyebrow">SoftNeos Suite</p>
                    <h1 id="titol" class="display-5 text-white mb-3">Del taller a la factura, sense perdre el fil</h1>
                    <p class="lead text-white-75 mb-4">Programari de gestió per a la petita i mitjana indústria: producció, costos, fitxatge i, ara, també pressupostos. Tot al núvol i connectat.</p>
                    <div class="d-flex flex-wrap gap-2">
                        <a class="btn btn-accent btn-lg" href="#moduls">Coneix els mòduls</a>
                        <a class="btn btn-outline-light btn-lg" href="{{ route('login') }}">Accés clients</a>
                    </div>
                </div>

                {{-- Decorative preview of the quotes module (static, not real data). --}}
                <div class="col-lg-6" aria-hidden="true">
                    <div class="quote-preview card shadow">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <div class="small text-secondary">Pressupost</div>
                                    <div class="fw-semibold text-primary">P-2026-0001</div>
                                </div>
                                <span class="badge text-bg-accent">Enviat</span>
                            </div>
                            <table class="table table-sm mb-3">
                                <tbody>
                                    <tr><td>Mecanitzat CNC · 10 h</td><td class="text-end">427,50 €</td></tr>
                                    <tr><td>Galvanitzat en calent · 200 kg</td><td class="text-end">190,00 €</td></tr>
                                </tbody>
                            </table>
                            <dl class="row small mb-0">
                                <dt class="col-8 fw-normal text-secondary">Base imposable</dt><dd class="col-4 text-end mb-1">617,50 €</dd>
                                <dt class="col-8 fw-normal text-secondary">IVA</dt><dd class="col-4 text-end mb-1">108,78 €</dd>
                                <dt class="col-8">Total</dt><dd class="col-4 text-end fw-semibold text-primary mb-0">726,28 €</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Qui som --------------------------------------------------------------- --}}
    <section id="qui-som" class="py-5" aria-labelledby="qui-som-titol">
        <div class="container py-lg-4">
            <div class="row g-4 align-items-center">
                <div class="col-lg-6">
                    <p class="eyebrow">Qui som</p>
                    <h2 id="qui-som-titol" class="mb-3">Programari pensat per a qui fabrica</h2>
                    <p>A SoftNeos desenvolupem programari propi per a empreses industrials que volen dades fiables sense complicar-se: saber què s'està fabricant, quant costa cada peça i quan s'ha de facturar.</p>
                    <p class="mb-0">Treballem a prop de cada client: implantacions curtes, formació a l'equip i suport de persones que coneixen el taller.</p>
                </div>
                <div class="col-lg-6">
                    <ul class="value-list list-unstyled row row-cols-1 row-cols-sm-3 g-3 mb-0">
                        <li class="col"><strong>Al núvol</strong><span>Sense servidors propis ni instal·lacions.</span></li>
                        <li class="col"><strong>Modular</strong><span>Activeu només els mòduls que necessiteu.</span></li>
                        <li class="col"><strong>A prop</strong><span>Implantació i suport amb el mateix equip.</span></li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    {{-- Mòduls (from the database) ----------------------------------------------- --}}
    <section id="moduls" class="py-5 bg-light" aria-labelledby="moduls-titol">
        <div class="container py-lg-4">
            <p class="eyebrow text-center">Mòduls</p>
            <h2 id="moduls-titol" class="text-center mb-4">Una suite, tota la gestió</h2>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                @forelse ($modules as $module)
                    <div class="col">
                        <x-feature-card :title="$module->name" :badge="$module->is_new ? 'Nou' : null">
                            {{ $module->summary }}
                        </x-feature-card>
                    </div>
                @empty
                    <p class="text-center text-secondary">Aviat publicarem els mòduls de la suite.</p>
                @endforelse
            </div>
        </div>
    </section>

    {{-- Nou mòdul de pressupostos ------------------------------------------------- --}}
    <section id="pressupostos" class="py-5" aria-labelledby="pressupostos-titol">
        <div class="container py-lg-4">
            <div class="row g-4 align-items-center">
                <div class="col-lg-7">
                    <p class="eyebrow">Nou mòdul</p>
                    <h2 id="pressupostos-titol" class="mb-3">Pressupostos dins de la suite</h2>
                    @if ($newModule)
                        <p class="lead">{{ $newModule->summary }}</p>
                    @endif
                    <ul class="check-list list-unstyled mb-4">
                        <li>Numeració correlativa per any, assignada pel servidor</li>
                        <li>Preus i IVA congelats en el moment de pressupostar</li>
                        <li>Estats clars: esborrany, enviat, acceptat i rebutjat</li>
                        <li>Vista imprimible per enviar al client</li>
                    </ul>
                    <a class="btn btn-primary btn-lg" href="{{ route('login') }}">Entra a l'àrea de clients</a>
                </div>
            </div>
        </div>
    </section>

    {{-- Contacte ----------------------------------------------------------------- --}}
    <section id="contacte" class="py-5 bg-light" aria-labelledby="contacte-titol">
        <div class="container py-lg-4">
            <div class="row g-4">
                <div class="col-lg-5">
                    <p class="eyebrow">Contacte</p>
                    <h2 id="contacte-titol" class="mb-3">Parlem del vostre taller</h2>
                    <p class="mb-0">Expliqueu-nos com treballeu i us ensenyarem la suite amb les vostres dades.</p>
                </div>
                <div class="col-lg-7">
                    <div class="row row-cols-1 row-cols-sm-2 g-3">
                        <div class="col">
                            <x-feature-card title="Correu electrònic">
                                <a href="mailto:info@softneos.test">info@softneos.test</a>
                            </x-feature-card>
                        </div>
                        <div class="col">
                            <x-feature-card title="Telèfon">
                                <a href="tel:+34900000000">900 000 000</a><br>
                                De dilluns a divendres, de 8 a 18 h
                            </x-feature-card>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-layouts.public>
