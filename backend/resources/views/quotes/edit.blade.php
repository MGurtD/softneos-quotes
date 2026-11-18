<x-layouts.app :title="$quote ? 'Pressupost '.$quote->number : 'Nou pressupost'">
    <nav aria-label="Ruta" class="mb-2">
        <a class="small" href="{{ route('quotes.index') }}">&larr; Tots els pressupostos</a>
    </nav>

    {{-- Angular custom element (frontend/src/app/features/quotes/quote-editor). --}}
    <sn-quote-editor @if ($quote) quote-id="{{ $quote->id }}" @endif></sn-quote-editor>
</x-layouts.app>
