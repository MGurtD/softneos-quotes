import { HttpClient, httpResource } from '@angular/common/http';
import { Injectable, inject } from '@angular/core';
import { Observable, map } from 'rxjs';

import {
  Customer,
  CustomerPayload,
  Paginated,
  ProductReference,
  ProductReferencePayload,
  VatType,
} from './api.models';

/** Search and page of a catalogue screen. */
export interface CatalogQuery {
  q: string;
  page: number;
}

const PAGE_SIZE = 10;

/**
 * Customers, product references and VAT types: the editor's selects and the
 * catalogue screens (/clients, /referencies).
 */
@Injectable({ providedIn: 'root' })
export class CatalogApi {
  private readonly http = inject(HttpClient);

  // Selects of the quote editor.

  customers() {
    return httpResource<Paginated<Customer>>(() => '/api/customers?per_page=100');
  }

  references() {
    return httpResource<Paginated<ProductReference>>(() => '/api/product-references?active=1&per_page=100');
  }

  vatTypes() {
    return httpResource<{ data: VatType[] }>(() => '/api/vat-types');
  }

  // Catalogue screens: the request is rebuilt (and re-fetched) when the query signal changes.

  customerPage(query: () => CatalogQuery) {
    return httpResource<Paginated<Customer>>(() => ({
      url: '/api/customers',
      params: { ...query(), per_page: PAGE_SIZE },
    }));
  }

  referencePage(query: () => CatalogQuery) {
    return httpResource<Paginated<ProductReference>>(() => ({
      url: '/api/product-references',
      params: { ...query(), per_page: PAGE_SIZE },
    }));
  }

  /** POST without id, PUT with id. */
  saveCustomer(payload: CustomerPayload, id?: number): Observable<Customer> {
    const request = id
      ? this.http.put<{ data: Customer }>(`/api/customers/${id}`, payload)
      : this.http.post<{ data: Customer }>('/api/customers', payload);
    return request.pipe(map((res) => res.data));
  }

  /** POST without id, PUT with id. */
  saveReference(payload: ProductReferencePayload, id?: number): Observable<ProductReference> {
    const request = id
      ? this.http.put<{ data: ProductReference }>(`/api/product-references/${id}`, payload)
      : this.http.post<{ data: ProductReference }>('/api/product-references', payload);
    return request.pipe(map((res) => res.data));
  }
}
