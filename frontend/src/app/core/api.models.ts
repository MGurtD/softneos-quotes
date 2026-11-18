/**
 * Shapes of the Laravel API Resources. Money and quantities are strings
 * ("427.50"), exactly as the API sends them: never converted to number
 * for calculations in the browser.
 */

export type QuoteStatusValue = 'draft' | 'sent' | 'accepted' | 'rejected';

export interface QuoteStatus {
  value: QuoteStatusValue;
  label: string;
  /** Allowed next states, decided by the server (QuoteStatus::canTransitionTo). */
  transitions: { value: QuoteStatusValue; label: string }[];
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

export interface QuoteLine {
  id: number;
  position: number;
  product_reference_id: number | null;
  description: string;
  quantity: string;
  unit_price: string;
  discount_pct: string;
  vat_type_id: number;
  vat_rate: string;
  base: string;
  vat_amount: string;
}

export interface Quote {
  id: number;
  number: string;
  status: QuoteStatus;
  issue_date: string;
  valid_until: string;
  notes: string | null;
  customer?: CustomerSummary;
  lines?: QuoteLine[];
  totals?: QuoteTotals;
}

/** Body of POST/PUT /api/quotes: never number, status, vat_rate or totals. */
export interface QuotePayload {
  customer_id: number;
  issue_date: string;
  valid_until: string;
  notes: string | null;
  lines: {
    product_reference_id: number | null;
    description: string;
    quantity: number;
    unit_price: number;
    discount_pct: number;
    vat_type_id: number;
  }[];
}

export interface VatType {
  id: number;
  name: string;
  rate: string;
}

export interface ProductReference {
  id: number;
  code: string;
  description: string;
  unit: string;
  unit_price: string;
  vat_type_id: number;
  active: boolean;
}

export interface Customer {
  id: number;
  name: string;
  tax_id: string;
}

/** Laravel 422 body: { message, errors: { field: [messages] } }. */
export interface ValidationErrorBody {
  message: string;
  errors?: Record<string, string[]>;
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
