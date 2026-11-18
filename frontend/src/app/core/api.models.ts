/**
 * Shapes of the Laravel API Resources. Money and quantities are strings
 * ("427.50"), exactly as the API sends them: never converted to number
 * for calculations in the browser.
 */

export type QuoteStatusValue = 'draft' | 'sent' | 'accepted' | 'rejected';

export interface QuoteStatus {
  value: QuoteStatusValue;
  label: string;
}

export interface CustomerSummary {
  id: number;
  name: string;
  tax_id: string;
}

export interface VatBreakdown {
  rate: string;
  base: string;
  vat: string;
}

export interface QuoteTotals {
  vat_breakdown: VatBreakdown[];
  subtotal: string;
  vat_total: string;
  total: string;
}

export interface Quote {
  id: number;
  number: string;
  status: QuoteStatus;
  issue_date: string;
  valid_until: string;
  notes: string | null;
  customer?: CustomerSummary;
  totals?: QuoteTotals;
}

/** Laravel paginator wrapped by a resource collection. */
export interface Paginated<T> {
  data: T[];
  meta: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
}
