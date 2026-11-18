import { HttpErrorResponse, HttpInterceptorFn } from '@angular/common/http';
import { catchError, throwError } from 'rxjs';

/**
 * Runs on every HttpClient request. If Laravel answers that the session or
 * the CSRF token has expired, send the user to the Blade login: after
 * logging in, Laravel's intended() brings them back to the page they were on.
 */
export const sessionExpiredInterceptor: HttpInterceptorFn = (req, next) => {
  return next(req).pipe(
    catchError((error: HttpErrorResponse) => {
      // 401: no session. 419: CSRF token expired. Both mean "log in again".
      if ([401, 419].includes(error.status)) {
        window.location.href = '/login';
      }

      return throwError(() => error);
    }),
  );
};
