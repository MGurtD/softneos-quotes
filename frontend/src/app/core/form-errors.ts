import { HttpErrorResponse } from '@angular/common/http';
import { AbstractControl, FormGroup } from '@angular/forms';

import { ValidationErrorBody } from './api.models';

const GENERIC_ERROR = 'No s’ha pogut completar l’operació. Torna-ho a provar.';

/**
 * Puts each Laravel 422 message on its form control as { server: message },
 * so it shows under the right field (e.g. the unique rule on tax_id).
 * Returns the messages that have no control, for a general alert.
 */
export function applyServerErrors(form: FormGroup, error: HttpErrorResponse): string[] {
  if (error.status !== 422) {
    return [GENERIC_ERROR];
  }
  const body = error.error as ValidationErrorBody;
  if (!body.errors) {
    return [body.message];
  }

  const unmatched: string[] = [];
  for (const [field, messages] of Object.entries(body.errors)) {
    const control = form.get(field);
    if (control) {
      control.setErrors({ server: messages[0] });
      control.markAsTouched();
    } else {
      unmatched.push(...messages);
    }
  }
  return unmatched;
}

/** Message for a touched invalid control: the server's first, then the client validators. */
export function controlError(control: AbstractControl): string | null {
  const errors = control.errors;
  if (!control.touched || !errors) {
    return null;
  }
  if (errors['server']) {
    return errors['server'];
  }
  if (errors['required']) {
    return 'Aquest camp és obligatori.';
  }
  if (errors['email']) {
    return 'Escriu un correu electrònic vàlid.';
  }
  if (errors['maxlength']) {
    return `Com a màxim ${errors['maxlength'].requiredLength} caràcters.`;
  }
  if (errors['min']) {
    return `El valor mínim és ${errors['min'].min}.`;
  }
  return 'Valor no vàlid.';
}
