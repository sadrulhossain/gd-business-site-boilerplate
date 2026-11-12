import { useId } from 'react'

export type FieldType = 'text' | 'textarea' | 'number' | 'checkbox' | 'select'

export interface FormFieldProps {
  label: string
  name: string
  type?: FieldType
  value: unknown
  onChange: (value: unknown) => void
  error?: string
  required?: boolean
  hint?: string
  options?: { value: string | number; label: string }[]
}

const control = 'w-full rounded-md border border-line bg-surface px-2.5 py-1.5 focus:outline-2 focus:outline-primary-500'

/** One labelled input with its validation error. The label is associated for screen readers and tests. */
export function FormField({ label, name, type = 'text', value, onChange, error, required, hint, options }: FormFieldProps) {
  const id = useId()
  const describedBy = error ? `${id}-error` : hint ? `${id}-hint` : undefined
  const common = { id, name, required, 'aria-invalid': error ? true : undefined, 'aria-describedby': describedBy }

  let input
  if (type === 'textarea') {
    input = <textarea {...common} rows={4} className={control} value={String(value ?? '')} onChange={(e) => onChange(e.target.value)} />
  } else if (type === 'select') {
    input = (
      <select {...common} className={control} value={String(value ?? '')} onChange={(e) => onChange(e.target.value)}>
        <option value="">-</option>
        {options?.map((o) => (
          <option key={o.value} value={o.value}>
            {o.label}
          </option>
        ))}
      </select>
    )
  } else if (type === 'checkbox') {
    input = <input {...common} type="checkbox" className="size-4" checked={Boolean(value)} onChange={(e) => onChange(e.target.checked)} />
  } else if (type === 'number') {
    input = (
      <input
        {...common}
        type="number"
        className={control}
        value={value === null || value === undefined ? '' : String(value)}
        onChange={(e) => onChange(e.target.value === '' ? null : Number(e.target.value))}
      />
    )
  } else {
    input = <input {...common} type="text" className={control} value={String(value ?? '')} onChange={(e) => onChange(e.target.value)} />
  }

  return (
    <div className={type === 'checkbox' ? 'flex items-center gap-2' : 'space-y-1'}>
      <label htmlFor={id} className="block text-sm text-ink-muted">
        {label}
        {required && <span aria-hidden="true"> *</span>}
      </label>
      {input}
      {hint && !error && (
        <p id={`${id}-hint`} className="text-sm text-ink-muted">
          {hint}
        </p>
      )}
      {error && (
        <p id={`${id}-error`} role="alert" className="text-sm text-danger">
          {error}
        </p>
      )}
    </div>
  )
}
