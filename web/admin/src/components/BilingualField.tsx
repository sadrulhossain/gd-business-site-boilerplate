import { useQuery } from '@tanstack/react-query'
import { api } from '../api'
import type { Locales } from '../types'
import { FormField, type FieldType } from './FormField'

/** Supported locales; `second` is the first one that is not the default (the language of the *_2nd columns). */
export function useLocales() {
  const query = useQuery({
    queryKey: ['locales'],
    staleTime: Infinity,
    queryFn: async () => ((await api.request('GET', '/v1/meta/locales')) as { data: Locales }).data,
  })
  const locales = query.data

  return { default: locales?.default, second: locales?.supported.find((code) => code !== locales.default) }
}

/**
 * A translatable field: `name` for the default language and `name_2nd` for the second, labelled from /meta/locales,
 * e.g. "Title (EN)" and "Title (FR)". With a single locale it renders just the one plain field.
 */
export function BilingualField({
  label,
  name,
  type = 'text',
  values,
  errors,
  required,
  onChange,
}: {
  label: string
  name: string
  type?: Extract<FieldType, 'text' | 'textarea' | 'richtext'>
  values: Record<string, unknown>
  errors: Record<string, string>
  required?: boolean
  onChange: (name: string, value: unknown) => void
}) {
  const locales = useLocales()
  const suffix = (code?: string) => (code && locales.second ? ` (${code.toUpperCase()})` : '')

  return (
    <>
      <FormField
        label={label + suffix(locales.default)}
        name={name}
        type={type}
        required={required}
        value={values[name]}
        error={errors[name]}
        onChange={(v) => onChange(name, v)}
      />
      {locales.second && (
        <FormField
          label={label + suffix(locales.second)}
          name={`${name}_2nd`}
          type={type}
          value={values[`${name}_2nd`]}
          error={errors[`${name}_2nd`]}
          onChange={(v) => onChange(`${name}_2nd`, v)}
        />
      )}
    </>
  )
}
