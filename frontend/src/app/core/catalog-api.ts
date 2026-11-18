import { httpResource } from '@angular/common/http';
import { Injectable } from '@angular/core';

import { Customer, Paginated, ProductReference, VatType } from './api.models';

/**
 * Read-only catalogues the quote editor needs for its selects.
 */
@Injectable({ providedIn: 'root' })
export class CatalogApi {
  customers() {
    return httpResource<Paginated<Customer>>(() => '/api/customers?per_page=100');
  }

  references() {
    return httpResource<Paginated<ProductReference>>(() => '/api/product-references?active=1&per_page=100');
  }

  vatTypes() {
    return httpResource<{ data: VatType[] }>(() => '/api/vat-types');
  }
}
