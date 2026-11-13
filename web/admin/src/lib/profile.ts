export const MIN_PASSWORD = 12

export interface PasswordForm {
  current_password: string
  password: string
  password_confirmation: string
}

/** Field => message for what the browser can already tell; the API validates again. Empty object = ok. */
export function checkPasswordChange(form: PasswordForm): Record<string, string> {
  const errors: Record<string, string> = {}
  if (form.current_password === '') errors.current_password = 'Enter your current password'
  if (form.password.length < MIN_PASSWORD) errors.password = `At least ${MIN_PASSWORD} characters`
  else if (form.password === form.current_password) errors.password = 'Choose a different password'
  if (form.password_confirmation !== form.password) errors.password_confirmation = 'Does not match'

  return errors
}
