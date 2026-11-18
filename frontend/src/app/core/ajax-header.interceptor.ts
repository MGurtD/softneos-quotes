import { HttpInterceptorFn } from '@angular/common/http';

/**
 * Marks every HttpClient request as AJAX, as axios does in a default Laravel
 * app. Without it Laravel stores API URLs such as /api/quotes as the
 * session's "previous URL", and the login could later redirect there.
 */
export const ajaxHeaderInterceptor: HttpInterceptorFn = (req, next) =>
  next(req.clone({ setHeaders: { 'X-Requested-With': 'XMLHttpRequest' } }));
