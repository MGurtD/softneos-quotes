import { provideHttpClient, withInterceptors } from '@angular/common/http';
import { ApplicationConfig, LOCALE_ID, provideBrowserGlobalErrorListeners } from '@angular/core';

import { sessionExpiredInterceptor } from './core/session-expired.interceptor';

/**
 * Global providers shared by every custom element. HttpClient sends the
 * XSRF-TOKEN cookie back as the X-XSRF-TOKEN header on relative URLs, the
 * same names Laravel uses, so CSRF needs no extra configuration.
 */
export const appConfig: ApplicationConfig = {
  providers: [
    provideBrowserGlobalErrorListeners(),
    provideHttpClient(withInterceptors([sessionExpiredInterceptor])),
    { provide: LOCALE_ID, useValue: 'ca' },
  ],
};
