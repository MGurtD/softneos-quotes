import { CurrencyPipe } from '@angular/common';
import { HttpErrorResponse } from '@angular/common/http';
import { ChangeDetectionStrategy, Component, inject, linkedSignal, signal } from '@angular/core';
import { toSignal } from '@angular/core/rxjs-interop';
import { FormControl, NonNullableFormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { debounceTime, distinctUntilChanged, map } from 'rxjs';

import { Paginated, ProductReference } from '../../../core/api.models';
import { CatalogApi } from '../../../core/catalog-api';
import { applyServerErrors, controlError } from '../../../core/form-errors';

/**
 * <sn-reference-list>: searchable product references table plus a side form
 * to create or edit, inside the Blade page /referencies.
 */
@Component({
  selector: 'sn-reference-list',
  imports: [ReactiveFormsModule, CurrencyPipe],
  templateUrl: './reference-list.html',
  styleUrl: './reference-list.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ReferenceList {
  private readonly catalog = inject(CatalogApi);
  private readonly fb = inject(NonNullableFormBuilder);

  protected readonly vatTypes = this.catalog.vatTypes();

  protected readonly search = new FormControl('', { nonNullable: true });
  private readonly term = toSignal(
    this.search.valueChanges.pipe(debounceTime(300), map((value) => value.trim()), distinctUntilChanged()),
    { initialValue: '' },
  );
  /** Back to page 1 whenever the search term changes. */
  protected readonly page = linkedSignal({ source: this.term, computation: () => 1 });

  protected readonly references = this.catalog.referencePage(() => ({ q: this.term(), page: this.page() }));
  /** Last page received: the table stays on screen while the next one loads. */
  protected readonly shown = linkedSignal<Paginated<ProductReference> | undefined, Paginated<ProductReference> | undefined>({
    source: this.references.value,
    computation: (value, previous) => value ?? previous?.value,
  });

  /** Reference being edited; null means the form creates a new one. */
  protected readonly editing = signal<ProductReference | null>(null);
  protected readonly saving = signal(false);
  protected readonly errors = signal<string[]>([]);
  protected readonly notice = signal<string | null>(null);

  protected readonly form = this.fb.group({
    code: ['', [Validators.required, Validators.maxLength(30)]],
    description: ['', [Validators.required, Validators.maxLength(255)]],
    unit: ['pza', [Validators.required, Validators.maxLength(10)]],
    unit_price: [0, [Validators.required, Validators.min(0)]],
    vat_type_id: this.fb.control<number | null>(null, Validators.required),
    active: [true],
  });

  protected readonly fieldError = controlError;

  protected startNew(): void {
    this.editing.set(null);
    this.form.reset();
    this.clearMessages();
  }

  protected edit(reference: ProductReference): void {
    this.editing.set(reference);
    this.form.reset({
      code: reference.code,
      description: reference.description,
      unit: reference.unit,
      unit_price: Number(reference.unit_price),
      vat_type_id: reference.vat_type_id,
      active: reference.active,
    });
    this.clearMessages();
  }

  protected save(): void {
    if (this.form.invalid) {
      this.form.markAllAsTouched();
      return;
    }
    const id = this.editing()?.id;
    const value = this.form.getRawValue();
    this.saving.set(true);
    this.clearMessages();
    this.catalog.saveReference({ ...value, vat_type_id: value.vat_type_id! }, id).subscribe({
      next: (reference) => {
        this.saving.set(false);
        this.editing.set(reference);
        this.notice.set(id ? 'Referència actualitzada.' : 'Referència creada.');
        this.references.reload();
      },
      error: (error: HttpErrorResponse) => {
        this.saving.set(false);
        this.errors.set(applyServerErrors(this.form, error));
      },
    });
  }

  private clearMessages(): void {
    this.errors.set([]);
    this.notice.set(null);
  }
}
