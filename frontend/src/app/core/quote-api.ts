import { HttpClient, httpResource } from '@angular/common/http';
import { Injectable, inject } from '@angular/core';
import { Observable, map } from 'rxjs';

import { Paginated, Quote, QuotePayload, QuoteStatusValue } from './api.models';

/**
 * HTTP access to /api/quotes. Relative URLs: the page and the API share the
 * same origin, so the Laravel session cookie travels with every request and
 * HttpClient adds the X-XSRF-TOKEN header to the writes.
 */
@Injectable({ providedIn: 'root' })
export class QuoteApi {
  private readonly http = inject(HttpClient);

  /** Reactive list: re-fetches whenever a signal read inside the callback changes. */
  list() {
    return httpResource<Paginated<Quote>>(() => '/api/quotes');
  }

  get(id: number): Observable<Quote> {
    return this.http.get<{ data: Quote }>(`/api/quotes/${id}`).pipe(map((res) => res.data));
  }

  create(payload: QuotePayload): Observable<Quote> {
    return this.http.post<{ data: Quote }>('/api/quotes', payload).pipe(map((res) => res.data));
  }

  update(id: number, payload: QuotePayload): Observable<Quote> {
    return this.http.put<{ data: Quote }>(`/api/quotes/${id}`, payload).pipe(map((res) => res.data));
  }

  changeStatus(id: number, status: QuoteStatusValue): Observable<Quote> {
    return this.http
      .patch<{ data: Quote }>(`/api/quotes/${id}/status`, { status })
      .pipe(map((res) => res.data));
  }

  delete(id: number): Observable<void> {
    return this.http.delete<void>(`/api/quotes/${id}`);
  }
}
