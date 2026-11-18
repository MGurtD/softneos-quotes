import { HttpErrorResponse } from '@angular/common/http';
import { ChangeDetectionStrategy, Component, inject, linkedSignal, signal } from '@angular/core';
import { toSignal } from '@angular/core/rxjs-interop';
import { FormControl, NonNullableFormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { debounceTime, distinctUntilChanged, map } from 'rxjs';

import { Paginated, Customer } from '../../../core/api.models';
import { CatalogApi } from '../../../core/catalog-api';
import { applyServerErrors, controlError } from '../../../core/form-errors';

/**
 * <sn-customer-list>: searchable customers table plus a side form to create
 * or edit, inside the Blade page /clients.
 */
@Component({
  selector: 'sn-customer-list',
  imports: [ReactiveFormsModule],
  templateUrl: './customer-list.html',
  styleUrl: './customer-list.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class CustomerList {
  private readonly catalog = inject(CatalogApi);
  private readonly fb = inject(NonNullableFormBuilder);

  protected readonly search = new FormControl('', { nonNullable: true });
  private readonly term = toSignal(
    this.search.valueChanges.pipe(debounceTime(300), map((value) => value.trim()), distinctUntilChanged()),
    { initialValue: '' },
  );
  /** Back to page 1 whenever the search term changes. */
  protected readonly page = linkedSignal({ source: this.term, computation: () => 1 });

  protected readonly customers = this.catalog.customerPage(() => ({ q: this.term(), page: this.page() }));
  /** Last page received: the table stays on screen while the next one loads. */
  protected readonly shown = linkedSignal<Paginated<Customer> | undefined, Paginated<Customer> | undefined>({
    source: this.customers.value,
    computation: (value, previous) => value ?? previous?.value,
  });

  /** Customer being edited; null means the form creates a new one. */
  protected readonly editing = signal<Customer | null>(null);
  protected readonly saving = signal(false);
  protected readonly errors = signal<string[]>([]);
  protected readonly notice = signal<string | null>(null);

  protected readonly form = this.fb.group({
    name: ['', [Validators.required, Validators.maxLength(255)]],
    tax_id: ['', [Validators.required, Validators.maxLength(20)]],
    email: ['', [Validators.email, Validators.maxLength(255)]],
    phone: ['', Validators.maxLength(30)],
    address: ['', Validators.maxLength(255)],
    city: ['', Validators.maxLength(100)],
    postal_code: ['', Validators.maxLength(10)],
  });

  protected readonly fieldError = controlError;

  protected startNew(): void {
    this.editing.set(null);
    this.form.reset();
    this.clearMessages();
  }

  protected edit(customer: Customer): void {
    this.editing.set(customer);
    this.form.reset({
      name: customer.name,
      tax_id: customer.tax_id,
      email: customer.email ?? '',
      phone: customer.phone ?? '',
      address: customer.address ?? '',
      city: customer.city ?? '',
      postal_code: customer.postal_code ?? '',
    });
    this.clearMessages();
  }

  protected save(): void {
    if (this.form.invalid) {
      this.form.markAllAsTouched();
      return;
    }
    const id = this.editing()?.id;
    this.saving.set(true);
    this.clearMessages();
    this.catalog.saveCustomer(this.form.getRawValue(), id).subscribe({
      next: (customer) => {
        this.saving.set(false);
        this.editing.set(customer);
        this.notice.set(id ? 'Client actualitzat.' : 'Client creat.');
        this.customers.reload();
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
