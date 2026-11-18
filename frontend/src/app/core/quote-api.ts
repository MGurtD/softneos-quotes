import { httpResource } from '@angular/common/http';
import { Injectable } from '@angular/core';

import { Paginated, Quote } from './api.models';

/**
 * HTTP access to /api/quotes. Relative URLs: the page and the API share the
 * same origin, so the Laravel session cookie travels with every request.
 */
@Injectable({ providedIn: 'root' })
export class QuoteApi {
  /** Reactive list: re-fetches whenever a signal read inside the callback changes. */
  list() {
    return httpResource<Paginated<Quote>>(() => '/api/quotes');
  }
}
