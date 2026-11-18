import { CurrencyPipe, DatePipe } from '@angular/common';
import { ChangeDetectionStrategy, Component, inject } from '@angular/core';

import { QuoteStatusValue } from '../../../core/api.models';
import { QuoteApi } from '../../../core/quote-api';

const STATUS_BADGE: Record<QuoteStatusValue, string> = {
  draft: 'text-bg-secondary',
  sent: 'text-bg-primary',
  accepted: 'text-bg-success',
  rejected: 'text-bg-danger',
};

/**
 * <sn-quote-list>: quotes table rendered inside the Blade page /pressupostos.
 */
@Component({
  selector: 'sn-quote-list',
  imports: [CurrencyPipe, DatePipe],
  templateUrl: './quote-list.html',
  styleUrl: './quote-list.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class QuoteList {
  protected readonly quotes = inject(QuoteApi).list();

  protected badgeClass(status: QuoteStatusValue): string {
    return STATUS_BADGE[status];
  }
}
