import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { useState } from 'react'
import { ApiError } from '@gd/api-client'
import { api } from '../api'
import { useAuth } from '../auth/store'
import { BilingualField } from '../components/BilingualField'
import { FormField } from '../components/FormField'
import { ImagePanel } from '../components/ImagePanel'
import { toast } from '../components/Toast'
import { IconButton } from '../components/IconButton'
import { fieldErrors } from '../lib/crud'
import { blanksToNull, getPath, setPath } from '../lib/object-path'
import { settingsKeys, type SingletonConfig } from '../settingsConfig'

type Values = Record<string, unknown>

/** What PATCH receives: the form-owned top-level keys only, blanks as null. */
export function settingsPayload(config: SingletonConfig, values: Values): Values {
  return Object.fromEntries(settingsKeys(config).map((key) => [key, blanksToNull(values[key])]))
}

export function SettingsPage({ config }: { config: SingletonConfig }) {
  const record = useQuery({
    queryKey: ['single', config.endpoint],
    queryFn: async () => ((await api.request('GET', config.endpoint)) as { data: Values }).data,
  })

  return (
    <section className="space-y-4">
      <h1 className="text-2xl font-semibold">{config.title}</h1>
      {record.isError ? (
        <p role="alert" className="text-danger">
          Could not load {config.title.toLowerCase()}.
        </p>
      ) : record.data ? (
        <SettingsForm config={config} initial={record.data} />
      ) : (
        <p className="text-ink-muted">Loading...</p>
      )}
    </section>
  )
}

function SettingsForm({ config, initial }: { config: SingletonConfig; initial: Values }) {
  const queryClient = useQueryClient()
  const canUpdate = useAuth((s) => s.can)(`${config.key}.update`)
  const [values, setValues] = useState<Values>(initial)
  const [errors, setErrors] = useState<Record<string, string>>({})
  const [active, setActive] = useState(config.tabs[0]!.id)

  const save = useMutation({
    mutationFn: () => api.request('PATCH', config.endpoint, { body: settingsPayload(config, values) }) as Promise<{ data: Values }>,
    onSuccess: ({ data }) => {
      setValues(data)
      queryClient.setQueryData(['single', config.endpoint], data)
      toast.success('Settings saved')
    },
    onError: (error) => {
      const found = fieldErrors(error)
      setErrors(found)
      // Jump to the first tab holding an invalid field.
      const tab = config.tabs.find((t) => t.fields.some((f) => found[f.name] || found[`${f.name}_2nd`]))
      if (tab) setActive(tab.id)
      toast.error(error instanceof ApiError && error.status === 422 ? 'Please fix the highlighted fields' : 'Could not save the settings')
    },
  })

  const set = (name: string, value: unknown) => setValues((v) => setPath(v, name, value))
  const tab = config.tabs.find((t) => t.id === active) ?? config.tabs[0]!

  return (
    <form
      className="space-y-4"
      noValidate
      onSubmit={(e) => {
        e.preventDefault()
        setErrors({})
        save.mutate()
      }}
    >
      <div role="tablist" aria-label={config.title} className={`flex gap-1 overflow-x-auto border-b border-line ${config.tabs.length < 2 ? 'hidden' : ''}`}>
        {config.tabs.map((t) => (
          <button
            key={t.id}
            type="button"
            role="tab"
            id={`tab-${t.id}`}
            aria-selected={t.id === active}
            aria-controls={`panel-${t.id}`}
            className={`-mb-px shrink-0 border-b-2 px-3 py-2 ${t.id === active ? 'border-primary-600 font-semibold text-primary-700' : 'border-transparent text-ink-muted hover:text-ink'}`}
            onClick={() => setActive(t.id)}
          >
            {t.label}
            {t.fields.some((f) => errors[f.name] || errors[`${f.name}_2nd`]) && <span className="text-danger"> !</span>}
          </button>
        ))}
      </div>

      <div role="tabpanel" id={`panel-${tab.id}`} aria-labelledby={`tab-${tab.id}`} className="max-w-2xl space-y-3">
        {tab.images?.map((img) => (
          <ImagePanel
            key={img.name}
            endpoint={`${config.endpoint}/${img.name}`}
            label={img.label}
            responseKey={img.name}
            allowIco={img.ico}
            hint={img.hint}
            previewClass={img.preview ?? 'max-h-40'}
            image={values[img.name] as { url: string } | null | undefined}
            onChange={(image) => {
              set(img.name, image)
              void queryClient.invalidateQueries({ queryKey: ['branding'] })
            }}
          />
        ))}
        {tab.fields.map((f) =>
          f.bilingual ? (
            <BilingualField
              key={f.name}
              label={f.label}
              name={f.name}
              type={f.type === 'textarea' ? 'textarea' : 'text'}
              required={f.required}
              values={values}
              errors={errors}
              onChange={set}
            />
          ) : (
            <FormField
              key={f.name}
              label={f.label}
              name={f.name}
              type={f.type}
              required={f.required}
              hint={f.hint}
              value={getPath(values, f.name)}
              error={errors[f.name]}
              onChange={(v) => set(f.name, v)}
            />
          ),
        )}
      </div>

      {canUpdate && (
        <IconButton showText type="submit" variant="primary" icon="save" label="Save" disabled={save.isPending} />
      )}
    </form>
  )
}
