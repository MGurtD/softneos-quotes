import { CurrencyPipe } from '@angular/common';
import { HttpErrorResponse } from '@angular/common/http';
import { ChangeDetectionStrategy, Component, computed, effect, inject, input, signal, untracked } from '@angular/core';
import { toSignal } from '@angular/core/rxjs-interop';
import { FormControl, FormGroup, NonNullableFormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';

import { Quote, QuotePayload, QuoteStatusValue, ValidationErrorBody } from '../../../core/api.models';
import { CatalogApi } from '../../../core/catalog-api';
import { QuoteApi } from '../../../core/quote-api';

type LineForm = FormGroup<{
  product_reference_id: FormControl<number | null>;
  description: FormControl<string>;
  quantity: FormControl<number>;
  unit_price: FormControl<number>;
  discount_pct: FormControl<number>;
  vat_type_id: FormControl<number | null>;
}>;

interface PreviewGroup {
  rate: number;
  base: number;
  vat: number;
}

/** Button copy for each target status (the server decides which ones are allowed). */
const ACTIONS: Record<QuoteStatusValue, { label: string; icon: string }> = {
  sent: { label: 'Envia', icon: 'bi-send' },
  accepted: { label: 'Accepta', icon: 'bi-check2-circle' },
  rejected: { label: 'Rebutja', icon: 'bi-x-circle' },
  draft: { label: 'Reobre', icon: 'bi-arrow-counterclockwise' },
};

const round2 = (value: number): number => Math.round((value + Number.EPSILON) * 100) / 100;
const isoDate = (date: Date): string => date.toISOString().slice(0, 10);

/**
 * <sn-quote-editor quote-id="5">: create or edit a quote inside the Blade
 * pages /pressupostos/nou and /pressupostos/{id}.
 *
 * The totals shown while typing are a preview. The figures that count are the
 * ones the server returns after saving (QuoteCalculator with bcmath).
 */
@Component({
  selector: 'sn-quote-editor',
  imports: [ReactiveFormsModule, CurrencyPipe],
  templateUrl: './quote-editor.html',
  styleUrl: './quote-editor.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class QuoteEditor {
  /** From the HTML attribute quote-id; absent when creating a new quote. */
  readonly quoteId = input<number | undefined, string | number | undefined>(undefined, {
    transform: (value) => (value === undefined || value === '' ? undefined : Number(value)),
  });

  private readonly api = inject(QuoteApi);
  private readonly fb = inject(NonNullableFormBuilder);
  private readonly catalog = inject(CatalogApi);

  protected readonly customers = this.catalog.customers();
  protected readonly references = this.catalog.references();
  protected readonly vatTypes = this.catalog.vatTypes();

  protected readonly quote = signal<Quote | null>(null);
  protected readonly loading = signal(false);
  protected readonly saving = signal(false);
  protected readonly errors = signal<string[]>([]);
  protected readonly notice = signal<string | null>(null);

  protected readonly editable = computed(() => (this.quote()?.status.value ?? 'draft') === 'draft');

  protected readonly form = this.fb.group({
    customer_id: this.fb.control<number | null>(null, Validators.required),
    issue_date: [isoDate(new Date()), Validators.required],
    valid_until: [isoDate(new Date(Date.now() + 30 * 86_400_000)), Validators.required],
    notes: [''],
    lines: this.fb.array<LineForm>([]),
  });

  private readonly formValue = toSignal(this.form.valueChanges, { initialValue: this.form.getRawValue() });

  /** Same formula as the server (round each line to cents), only for live feedback. */
  protected readonly preview = computed(() => {
    const rates = new Map((this.vatTypes.value()?.data ?? []).map((vat) => [vat.id, Number(vat.rate)]));
    const groups = new Map<number, PreviewGroup>();

    for (const line of this.formValue().lines ?? []) {
      const rate = rates.get(line.vat_type_id ?? -1);
      if (rate === undefined) {
        continue;
      }
      const base = round2((line.quantity ?? 0) * (line.unit_price ?? 0) * (1 - (line.discount_pct ?? 0) / 100));
      const group = groups.get(rate) ?? { rate, base: 0, vat: 0 };
      group.base = round2(group.base + base);
      group.vat = round2(group.vat + round2((base * rate) / 100));
      groups.set(rate, group);
    }

    const breakdown = [...groups.values()].sort((a, b) => b.rate - a.rate);
    const subtotal = round2(breakdown.reduce((sum, g) => sum + g.base, 0));
    const vatTotal = round2(breakdown.reduce((sum, g) => sum + g.vat, 0));

    return { breakdown, subtotal, vatTotal, total: round2(subtotal + vatTotal) };
  });

  /** While editing, the live preview; once locked, the figures the server calculated. */
  protected readonly totals = computed(() => {
    const saved = this.quote()?.totals;
    if (this.editable() || !saved) {
      return this.preview();
    }
    return {
      breakdown: saved.vat_breakdown.map((g) => ({ rate: Number(g.rate), base: Number(g.base), vat: Number(g.vat) })),
      subtotal: Number(saved.subtotal),
      vatTotal: Number(saved.vat_total),
      total: Number(saved.total),
    };
  });

  constructor() {
    effect(() => {
      const id = this.quoteId();
      if (id) {
        untracked(() => this.load(id));
      } else {
        untracked(() => this.form.controls.lines.length === 0 && this.addLine());
      }
    });
  }

  protected get lines() {
    return this.form.controls.lines;
  }

  protected addLine(): void {
    this.lines.push(this.newLine());
  }

  protected removeLine(index: number): void {
    this.lines.removeAt(index);
  }

  /** Picking a catalogue reference proposes its description, price and VAT; the user may change them. */
  protected onReferenceChange(index: number): void {
    const line = this.lines.at(index);
    const reference = this.references.value()?.data.find((ref) => ref.id === line.controls.product_reference_id.value);
    if (reference) {
      line.patchValue({
        description: reference.description,
        unit_price: Number(reference.unit_price),
        vat_type_id: reference.vat_type_id,
      });
    }
  }

  protected lineBase(index: number): number {
    const line = this.formValue().lines?.[index];
    return line ? round2((line.quantity ?? 0) * (line.unit_price ?? 0) * (1 - (line.discount_pct ?? 0) / 100)) : 0;
  }

  protected actionFor(status: QuoteStatusValue): { label: string; icon: string } {
    return ACTIONS[status];
  }

  protected save(): void {
    this.dropBlankLines();
    if (this.lines.length === 0) {
      this.addLine();
      this.errors.set(['Afegeix almenys una línia al pressupost.']);
      return;
    }
    if (this.form.invalid) {
      this.form.markAllAsTouched();
      this.errors.set(['Revisa els camps marcats en vermell.']);
      return;
    }

    const id = this.quote()?.id;
    const payload = this.payload();
    const request = id ? this.api.update(id, payload) : this.api.create(payload);

    this.saving.set(true);
    this.errors.set([]);
    request.subscribe({
      next: (quote) => {
        if (!id) {
          window.location.href = `/pressupostos/${quote.id}`;
          return;
        }
        this.apply(quote);
        this.notice.set('Pressupost desat.');
        this.saving.set(false);
      },
      error: (error: HttpErrorResponse) => this.fail(error),
    });
  }

  protected changeStatus(target: QuoteStatusValue): void {
    const id = this.quote()?.id;
    if (!id) {
      return;
    }
    this.saving.set(true);
    this.errors.set([]);
    this.api.changeStatus(id, target).subscribe({
      next: (quote) => {
        this.apply(quote);
        this.notice.set(`Estat actualitzat: ${quote.status.label}.`);
        this.saving.set(false);
      },
      error: (error: HttpErrorResponse) => this.fail(error),
    });
  }

  protected remove(): void {
    const id = this.quote()?.id;
    if (!id || !window.confirm('Vols esborrar aquest esborrany? No es pot desfer.')) {
      return;
    }
    this.api.delete(id).subscribe({
      next: () => (window.location.href = '/pressupostos'),
      error: (error: HttpErrorResponse) => this.fail(error),
    });
  }

  private load(id: number): void {
    this.loading.set(true);
    this.api.get(id).subscribe({
      next: (quote) => {
        this.apply(quote);
        this.loading.set(false);
      },
      error: (error: HttpErrorResponse) => {
        this.loading.set(false);
        this.fail(error);
      },
    });
  }

  /** Puts the server's version of the quote in the form (snapshots included). */
  private apply(quote: Quote): void {
    this.quote.set(quote);
    this.form.patchValue({
      customer_id: quote.customer?.id ?? null,
      issue_date: quote.issue_date,
      valid_until: quote.valid_until,
      notes: quote.notes ?? '',
    });
    this.lines.clear();
    for (const line of quote.lines ?? []) {
      this.lines.push(
        this.newLine({
          product_reference_id: line.product_reference_id,
          description: line.description,
          quantity: Number(line.quantity),
          unit_price: Number(line.unit_price),
          discount_pct: Number(line.discount_pct),
          vat_type_id: line.vat_type_id,
        }),
      );
    }
    if (quote.status.value === 'draft') {
      this.form.enable();
    } else {
      this.form.disable();
    }
    this.form.markAsPristine();
  }

  /**
   * A line with no reference and no description was never filled in (for
   * example the one left after "Afegeix línia"): ignore it instead of
   * blocking the save with validation errors.
   */
  private dropBlankLines(): void {
    for (let i = this.lines.length - 1; i >= 0; i--) {
      const line = this.lines.at(i).getRawValue();
      if (line.product_reference_id === null && line.description.trim() === '') {
        this.lines.removeAt(i);
      }
    }
  }

  private fail(error: HttpErrorResponse): void {
    this.saving.set(false);
    this.notice.set(null);
    if (error.status === 422) {
      const body = error.error as ValidationErrorBody;
      this.errors.set(body.errors ? Object.values(body.errors).flat() : [body.message]);
    } else {
      this.errors.set(['No s’ha pogut completar l’operació. Torna-ho a provar.']);
    }
  }

  private payload(): QuotePayload {
    const value = this.form.getRawValue();
    return {
      customer_id: value.customer_id!,
      issue_date: value.issue_date,
      valid_until: value.valid_until,
      notes: value.notes || null,
      lines: value.lines.map((line) => ({ ...line, vat_type_id: line.vat_type_id! })),
    };
  }

  private newLine(values?: Partial<ReturnType<LineForm['getRawValue']>>): LineForm {
    return this.fb.group({
      product_reference_id: this.fb.control<number | null>(values?.product_reference_id ?? null),
      description: [values?.description ?? '', Validators.required],
      quantity: [values?.quantity ?? 1, [Validators.required, Validators.min(0.001)]],
      unit_price: [values?.unit_price ?? 0, [Validators.required, Validators.min(0)]],
      discount_pct: [values?.discount_pct ?? 0, [Validators.min(0), Validators.max(100)]],
      vat_type_id: this.fb.control<number | null>(values?.vat_type_id ?? null, Validators.required),
    });
  }
}
