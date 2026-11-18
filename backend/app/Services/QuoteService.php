<?php

namespace App\Services;

use App\Enums\QuoteStatus;
use App\Models\ProductReference;
use App\Models\Quote;
use App\Models\VatType;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Write operations on quotes. Business rules that depend on the quote's
 * state live here (not in controllers) so the API, the Blade views and the
 * tests share them. Rule violations are thrown as ValidationException,
 * which Laravel renders as a 422 with the standard {message, errors} body.
 */
class QuoteService
{
    public function __construct(private QuoteNumberGenerator $numbers) {}

    /**
     * @param  array<string, mixed>  $data  validated SaveQuoteRequest data
     */
    public function create(array $data): Quote
    {
        return DB::transaction(function () use ($data): Quote {
            $quote = new Quote(Arr::except($data, 'lines'));
            $quote->number = $this->numbers->next(Carbon::parse($data['issue_date']));
            $quote->status = QuoteStatus::Draft;
            $quote->save();

            $this->replaceLines($quote, $data['lines']);

            return $quote;
        });
    }

    /**
     * Updates the header and replaces all lines (the quote is saved as a whole).
     *
     * @param  array<string, mixed>  $data  validated SaveQuoteRequest data
     */
    public function update(Quote $quote, array $data): Quote
    {
        $this->ensureEditable($quote);

        return DB::transaction(function () use ($quote, $data): Quote {
            $quote->update(Arr::except($data, 'lines'));
            $this->replaceLines($quote, $data['lines']);

            return $quote;
        });
    }

    public function delete(Quote $quote): void
    {
        $this->ensureEditable($quote);

        $quote->delete();
    }

    public function changeStatus(Quote $quote, QuoteStatus $target): Quote
    {
        if (! $quote->status->canTransitionTo($target)) {
            throw ValidationException::withMessages([
                'status' => sprintf(
                    'No se puede pasar un presupuesto de «%s» a «%s».',
                    $quote->status->label(),
                    $target->label(),
                ),
            ]);
        }

        if ($target === QuoteStatus::Sent && ! $quote->lines()->exists()) {
            throw ValidationException::withMessages([
                'status' => 'No se puede enviar un presupuesto sin líneas.',
            ]);
        }

        $quote->status = $target;
        $quote->save();

        return $quote;
    }

    private function ensureEditable(Quote $quote): void
    {
        if (! $quote->status->isEditable()) {
            throw ValidationException::withMessages([
                'status' => sprintf(
                    'Solo se pueden modificar o borrar presupuestos en borrador. Este está «%s».',
                    $quote->status->label(),
                ),
            ]);
        }
    }

    /**
     * @param  list<array<string, mixed>>  $lines
     */
    private function replaceLines(Quote $quote, array $lines): void
    {
        $quote->lines()->delete();

        // Two queries for all lines instead of two per line.
        $references = ProductReference::findMany(Arr::pluck($lines, 'product_reference_id'))->keyBy('id');
        $vatTypes = VatType::all()->keyBy('id');

        foreach (array_values($lines) as $index => $line) {
            $quote->lines()->create(
                $this->buildLine($line, $index + 1, $references, $vatTypes)
            );
        }
    }

    /**
     * Turns a validated input line into QuoteLine attributes, applying the
     * snapshot rule: description, unit_price and vat_type_id come from the
     * product reference when not sent; vat_rate is always copied from the
     * chosen VAT type (the client never sends it).
     *
     * @param  array<string, mixed>  $line
     * @param  Collection<int, ProductReference>  $references  keyed by id
     * @param  Collection<int, VatType>  $vatTypes  keyed by id
     * @return array<string, mixed>
     */
    private function buildLine(array $line, int $position, Collection $references, Collection $vatTypes): array
    {
        $reference = $references->get($line['product_reference_id'] ?? null);
        $vatTypeId = $line['vat_type_id'] ?? $reference?->vat_type_id;
        $vatType = $vatTypes->get($vatTypeId);

        return [
            'position' => $position,
            'product_reference_id' => $line['product_reference_id'] ?? null,
            'description' => $line['description'] ?? $reference?->description,
            'quantity' => $line['quantity'],
            'unit_price' => $line['unit_price'] ?? $reference?->unit_price,
            'discount_pct' => $line['discount_pct'] ?? '0',
            'vat_type_id' => $vatTypeId,
            'vat_rate' => $vatType?->rate ?? '0',
        ];
    }
}
