import { registerLocaleData } from '@angular/common';
import localeCa from '@angular/common/locales/ca';
import { Type } from '@angular/core';
import { createCustomElement } from '@angular/elements';
import { createApplication } from '@angular/platform-browser';

import { appConfig } from './app/app.config';
import { QuoteEditor } from './app/features/quotes/quote-editor/quote-editor';
import { QuoteList } from './app/features/quotes/quote-list/quote-list';
import { CustomerList } from './app/features/customers/customer-list/customer-list';
import { ReferenceList } from './app/features/references/reference-list/reference-list';

/**
 * There is no root component: Laravel renders every page with Blade and
 * these Angular components are registered as custom elements that Blade
 * places like any other HTML tag, e.g. <sn-quote-list></sn-quote-list>.
 */
const elements: Record<string, Type<unknown>> = {
  'sn-quote-list': QuoteList,
  'sn-quote-editor': QuoteEditor,
  'sn-customer-list': CustomerList,
  'sn-reference-list': ReferenceList,
};

registerLocaleData(localeCa);

createApplication(appConfig)
  .then((app) => {
    for (const [tag, component] of Object.entries(elements)) {
      if (!customElements.get(tag)) {
        customElements.define(tag, createCustomElement(component, { injector: app.injector }));
      }
    }
  })
  .catch((err) => console.error(err));
