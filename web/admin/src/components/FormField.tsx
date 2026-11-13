import { useId } from 'react'
import { RichText } from './RichText'

export type FieldType = 'text' | 'textarea' | 'number' | 'checkbox' | 'select' | 'password' | 'checkgroup' | 'richtext'

export interface FieldOption {
  value: string | number
  label: string
  /** checkgroup only: options sharing a group form one row of a matrix, the label being the column. */
  group?: string
}

export interface FormFieldProps {
  label: string
  name: string
  type?: FieldType
  value: unknown
  onChange: (value: unknown) => void
  error?: string
  required?: boolean
  hint?: string
  options?: FieldOption[]
}

const control = 'field'

/** One labelled input with its validation error. The label is associated for screen readers and tests. */
export function FormField({ label, name, type = 'text', value, onChange, error, required, hint, options }: FormFieldProps) {
  const id = useId()
  const describedBy = error ? `${id}-error` : hint ? `${id}-hint` : undefined
  const common = { id, name, required, 'aria-invalid': error ? true : undefined, 'aria-describedby': describedBy }

  let input
  if (type === 'textarea') {
    input = <textarea {...common} rows={4} className={control} value={String(value ?? '')} onChange={(e) => onChange(e.target.value)} />
  } else if (type === 'richtext') {
    input = <RichText id={id} label={label} value={String(value ?? '')} onChange={onChange} />
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
  } else if (type === 'checkgroup') {
    input = <CheckGroup id={id} name={name} label={label} value={Array.isArray(value) ? (value as string[]) : []} options={options ?? []} onChange={onChange} />
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
    input = <input {...common} type={type === 'password' ? 'password' : 'text'} autoComplete={type === 'password' ? 'new-password' : undefined} className={control} value={String(value ?? '')} onChange={(e) => onChange(e.target.value)} />
  }

  return (
    <div className={type === 'checkbox' ? 'flex items-center gap-2' : 'space-y-1'}>
      <label htmlFor={type === 'checkgroup' ? undefined : id} className="block text-sm text-ink-muted">
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

/**
 * Several checkboxes writing one string[]. With grouped options it renders a matrix: one row per group, one column per
 * distinct label (e.g. rows = modules, columns = view/create/update/delete); a missing cell is simply empty.
 */
function CheckGroup({
  label,
  value,
  options,
  onChange,
  ...aria
}: {
  label: string
  value: string[]
  options: FieldOption[]
  onChange: (value: unknown) => void
  id: string
  name: string
}) {
  const toggle = (option: FieldOption, on: boolean) => {
    const key = String(option.value)
    onChange(on ? [...value.filter((v) => v !== key), key] : value.filter((v) => v !== key))
  }
  const box = (option: FieldOption, name = option.label) => (
    <input type="checkbox" className="size-4" aria-label={`${label}: ${name}`} checked={value.includes(String(option.value))} onChange={(e) => toggle(option, e.target.checked)} />
  )

  if (!options.some((o) => o.group)) {
    return (
      <div id={aria.id} role="group" aria-label={label} className="flex flex-wrap gap-x-4 gap-y-1">
        {options.map((o) => (
          <label key={o.value} className="flex items-center gap-2">
            {box(o)}
            {o.label}
          </label>
        ))}
      </div>
    )
  }

  const groups = [...new Set(options.map((o) => o.group ?? ''))]
  const columns = [...new Set(options.map((o) => o.label))]

  return (
    <div id={aria.id} className="overflow-x-auto rounded-md border border-line">
      <table className="w-full text-left text-sm">
        <thead className="border-b border-line bg-canvas text-ink-muted">
          <tr>
            <th scope="col" className="px-2 py-1.5" />
            {columns.map((c) => (
              <th key={c} scope="col" className="px-2 py-1.5 font-semibold">
                {c}
              </th>
            ))}
          </tr>
        </thead>
        <tbody>
          {groups.map((group) => (
            <tr key={group} className="border-b border-line last:border-0">
              <th scope="row" className="px-2 py-1.5 font-normal">
                {group}
              </th>
              {columns.map((c) => {
                const option = options.find((o) => (o.group ?? '') === group && o.label === c)

                return <td key={c} className="px-2 py-1.5">{option && box(option, `${group} ${c}`)}</td>
              })}
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  )
}
