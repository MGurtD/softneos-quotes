@php
    use Illuminate\Support\Number;

    // Display only: amounts are already exact 2-decimal strings from QuoteCalculator.
    $money = fn (string $amount): string => Number::currency((float) $amount, in: 'EUR', locale: 'ca');
    $percent = fn (string $rate): string => Number::format((float) $rate, maxPrecision: 2, locale: 'ca').' %';
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pressupost {{ $quote->number }} · SoftNeos</title>

    @fonts
    @vite(['resources/scss/app.scss'])
</head>
<body class="print-body">
    <nav class="print-toolbar container d-flex justify-content-between align-items-center py-3" aria-label="Accions">
        <a class="link-secondary" href="{{ config('app.frontend_url') }}">&larr; Tornar a l’àrea de clients</a>
        <button type="button" class="btn btn-primary" onclick="window.print()">Imprimeix</button>
    </nav>

    <main class="print-sheet">
        <header class="d-flex justify-content-between align-items-start gap-4 mb-5">
            <div>
                <p class="brand-logo mb-1">Soft<span>Neos</span></p>
                <p class="small text-secondary mb-0">
                    SoftNeos (projecte de demostració)<br>
                    info@softneos.test · 900 000 000
                </p>
            </div>
            <div class="text-end">
                <h1 class="h3 mb-1">Pressupost</h1>
                <p class="fs-5 fw-semibold text-primary mb-1">{{ $quote->number }}</p>
                <span class="badge text-bg-accent">{{ $quote->status->label() }}</span>
            </div>
        </header>

        <section class="row g-4 mb-4" aria-label="Dades del pressupost">
            <div class="col-7">
                <h2 class="print-label">Client</h2>
                <p class="mb-0">
                    <strong>{{ $quote->customer->name }}</strong><br>
                    NIF: {{ $quote->customer->tax_id }}<br>
                    @if ($quote->customer->address)
                        {{ $quote->customer->address }}<br>
                    @endif
                    {{ trim($quote->customer->postal_code.' '.$quote->customer->city) }}
                </p>
            </div>
            <div class="col-5">
                <dl class="row mb-0">
                    <dt class="col-6 print-label">Data d’emissió</dt>
                    <dd class="col-6 text-end">{{ $quote->issue_date->format('d/m/Y') }}</dd>
                    <dt class="col-6 print-label">Vàlid fins al</dt>
                    <dd class="col-6 text-end">{{ $quote->valid_until->format('d/m/Y') }}</dd>
                </dl>
            </div>
        </section>

        <table class="table print-table align-middle">
            <thead>
                <tr>
                    <th scope="col">Descripció</th>
                    <th scope="col" class="text-end">Quantitat</th>
                    <th scope="col" class="text-end">Preu</th>
                    <th scope="col" class="text-end">Dte.</th>
                    <th scope="col" class="text-end">IVA</th>
                    <th scope="col" class="text-end">Base</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($lines as ['line' => $line, 'amounts' => $amounts])
                    <tr>
                        <td>{{ $line->description }}</td>
                        <td class="text-end">{{ Number::format((float) $line->quantity, maxPrecision: 3, locale: 'ca') }}</td>
                        <td class="text-end">{{ $money($line->unit_price) }}</td>
                        <td class="text-end">{{ (float) $line->discount_pct > 0 ? $percent($line->discount_pct) : '—' }}</td>
                        <td class="text-end">{{ $percent($line->vat_rate) }}</td>
                        <td class="text-end">{{ $money($amounts->base) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="row justify-content-end">
            <div class="col-7">
                <table class="table table-sm print-totals">
                    <thead>
                        <tr>
                            <th scope="col">Tipus d’IVA</th>
                            <th scope="col" class="text-end">Base</th>
                            <th scope="col" class="text-end">Quota</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($totals->vatBreakdown as $group)
                            <tr>
                                <td>{{ $percent($group['rate']) }}</td>
                                <td class="text-end">{{ $money($group['base']) }}</td>
                                <td class="text-end">{{ $money($group['vat']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th scope="row">Base imposable</th>
                            <td colspan="2" class="text-end">{{ $money($totals->subtotal) }}</td>
                        </tr>
                        <tr>
                            <th scope="row">Total IVA</th>
                            <td colspan="2" class="text-end">{{ $money($totals->vatTotal) }}</td>
                        </tr>
                        <tr class="print-grand-total">
                            <th scope="row">Total</th>
                            <td colspan="2" class="text-end">{{ $money($totals->total) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        @if ($quote->notes)
            <section class="mt-4">
                <h2 class="print-label">Observacions</h2>
                <p class="mb-0">{{ $quote->notes }}</p>
            </section>
        @endif
    </main>
</body>
</html>
