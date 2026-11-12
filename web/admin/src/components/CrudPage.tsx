import { keepPreviousData, useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { useState, type ReactNode } from 'react'
import { ApiError } from '@gd/api-client'
import { api } from '../api'
import { useAuth } from '../auth/store'
import type { Listing } from '../types'
import { BilingualField } from './BilingualField'
import { ConfirmDialog, Modal } from './ConfirmDialog'
import { DataTable, type Column } from './DataTable'
import { FormField, type FieldType } from './FormField'
import { toast } from './Toast'

type Row = Record<string, unknown> & { id: number }

export interface CrudField {
  name: string
  label: string
  type?: FieldType
  /** Renders `name` and `name_2nd`, labelled with the locale codes. Text and textarea only. */
  bilingual?: boolean
  required?: boolean
  hint?: string
  options?: { value: string | number; label: string }[]
  /** Starting value on create. */
  initial?: unknown
}

/**
 * Everything a module needs to get a list + create/edit/delete screen. Add an object to modules.ts; no new page code.
 * Permissions are `${key}.view|create|update|delete`; buttons the user may not use are not rendered.
 */
export interface CrudConfig {
  key: string
  path: string
  title: string
  /** Collection endpoint, e.g. `/v1/faqs`; rows live at `${endpoint}/${id}`. */
  endpoint: string
  columns: Column<Row>[]
  fields: CrudField[]
  /** Singular noun for dialogs: "FAQ". */
  noun: string
  /** Text identifying a row in the delete confirmation. */
  rowLabel?: (row: Row) => string
  /** Seeded, edit-only modules (sections) turn these off. */
  canCreate?: boolean
  canDelete?: boolean
}

function blank(fields: CrudField[]): Record<string, unknown> {
  return Object.fromEntries(fields.flatMap((f) => (f.bilingual ? [[f.name, f.initial ?? ''], [`${f.name}_2nd`, '']] : [[f.name, f.initial ?? (f.type === 'checkbox' ? false : '')]])))
}

/** Field values sent to the API: only configured fields (and their _2nd twins), `''` for optional text becomes null. */
function payload(fields: CrudField[], values: Record<string, unknown>): Record<string, unknown> {
  const entries = fields.flatMap((f) =>
    (f.bilingual ? [f.name, `${f.name}_2nd`] : [f.name]).map((n) => ({ n, v: values[n] === '' ? null : values[n], type: f.type })),
  )

  // A blank number is left out: the API takes absent, not null, for those.
  return Object.fromEntries(entries.filter((e) => !(e.type === 'number' && e.v === null)).map((e) => [e.n, e.v]))
}

function fieldErrors(error: unknown): Record<string, string> {
  if (!(error instanceof ApiError) || !error.details) return {}

  return Object.fromEntries(Object.entries(error.details).map(([name, messages]) => [name, messages[0] ?? '']))
}

export function CrudPage({ config }: { config: CrudConfig }) {
  const queryClient = useQueryClient()
  const can = useAuth((s) => s.can)
  const [page, setPage] = useState(1)
  const [editing, setEditing] = useState<Row | 'new' | null>(null)
  const [deleting, setDeleting] = useState<Row | null>(null)

  const canCreate = config.canCreate !== false && can(`${config.key}.create`)
  const canUpdate = can(`${config.key}.update`)
  const canDelete = config.canDelete !== false && can(`${config.key}.delete`)

  const listKey = ['crud', config.endpoint, page]
  const list = useQuery({
    queryKey: listKey,
    placeholderData: keepPreviousData,
    queryFn: async () => (await api.request('GET', config.endpoint, { query: { page } })) as Listing<Row>,
  })

  const refresh = () => queryClient.invalidateQueries({ queryKey: ['crud', config.endpoint] })

  const remove = useMutation({
    mutationFn: (row: Row) => api.request('DELETE', `${config.endpoint}/${row.id}`),
    onSuccess: () => {
      toast.success(`${config.noun} deleted`)
      setDeleting(null)
      void refresh()
    },
    onError: () => toast.error(`Could not delete the ${config.noun.toLowerCase()}`),
  })

  const rows = list.data?.data ?? []
  const meta = list.data?.meta
  const actions = (row: Row): ReactNode => (
    <span className="inline-flex gap-2">
      {canUpdate && (
        <button type="button" className="text-primary-600 hover:underline" onClick={() => setEditing(row)}>
          Edit
        </button>
      )}
      {canDelete && (
        <button type="button" className="text-danger hover:underline" onClick={() => setDeleting(row)}>
          Delete
        </button>
      )}
    </span>
  )

  return (
    <section className="space-y-4">
      <header className="flex items-center justify-between">
        <h1 className="text-2xl font-semibold">{config.title}</h1>
        {canCreate && (
          <button type="button" className="rounded-md bg-primary-600 px-3 py-1.5 text-white hover:bg-primary-700" onClick={() => setEditing('new')}>
            New {config.noun.toLowerCase()}
          </button>
        )}
      </header>

      {list.isError ? (
        <p role="alert" className="text-danger">
          Could not load {config.title.toLowerCase()}.
        </p>
      ) : (
        <DataTable
          columns={config.columns}
          rows={rows}
          rowKey={(r) => r.id}
          loading={list.isPending}
          actions={canUpdate || canDelete ? actions : undefined}
        />
      )}

      {meta && meta.last_page > 1 && (
        <nav className="flex items-center justify-end gap-3" aria-label="Pagination">
          <button type="button" disabled={page <= 1} className="disabled:opacity-40" onClick={() => setPage(page - 1)}>
            Previous
          </button>
          <span className="text-ink-muted">
            {meta.current_page} / {meta.last_page}
          </span>
          <button type="button" disabled={page >= meta.last_page} className="disabled:opacity-40" onClick={() => setPage(page + 1)}>
            Next
          </button>
        </nav>
      )}

      <Modal open={editing !== null} title={editing === 'new' ? `New ${config.noun.toLowerCase()}` : `Edit ${config.noun.toLowerCase()}`} onClose={() => setEditing(null)}>
        {editing !== null && (
          <CrudForm
            key={editing === 'new' ? 'new' : editing.id}
            config={config}
            row={editing === 'new' ? null : editing}
            onDone={() => {
              setEditing(null)
              void refresh()
            }}
            onCancel={() => setEditing(null)}
          />
        )}
      </Modal>

      <ConfirmDialog
        open={deleting !== null}
        title={`Delete ${config.noun.toLowerCase()}`}
        message={`Delete ${deleting && config.rowLabel ? `"${config.rowLabel(deleting)}"` : `this ${config.noun.toLowerCase()}`}? This cannot be undone.`}
        confirmLabel="Delete"
        danger
        busy={remove.isPending}
        onConfirm={() => deleting && remove.mutate(deleting)}
        onCancel={() => setDeleting(null)}
      />
    </section>
  )
}

function CrudForm({ config, row, onDone, onCancel }: { config: CrudConfig; row: Row | null; onDone: () => void; onCancel: () => void }) {
  const [values, setValues] = useState<Record<string, unknown>>(() => ({ ...blank(config.fields), ...(row ?? {}) }))
  const [errors, setErrors] = useState<Record<string, string>>({})
  const set = (name: string, value: unknown) => setValues((v) => ({ ...v, [name]: value }))

  const save = useMutation({
    mutationFn: () =>
      row
        ? api.request('PATCH', `${config.endpoint}/${row.id}`, { body: payload(config.fields, values) })
        : api.request('POST', config.endpoint, { body: payload(config.fields, values) }),
    onSuccess: () => {
      toast.success(`${config.noun} saved`)
      onDone()
    },
    onError: (error) => {
      setErrors(fieldErrors(error))
      toast.error(error instanceof ApiError && error.status === 422 ? 'Please fix the highlighted fields' : `Could not save the ${config.noun.toLowerCase()}`)
    },
  })

  return (
    <form
      className="space-y-3"
      noValidate
      onSubmit={(e) => {
        e.preventDefault()
        setErrors({})
        save.mutate()
      }}
    >
      {config.fields.map((f) =>
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
            options={f.options}
            value={values[f.name]}
            error={errors[f.name]}
            onChange={(v) => set(f.name, v)}
          />
        ),
      )}
      <div className="flex justify-end gap-2 pt-2">
        <button type="button" className="rounded-md border border-line bg-surface px-3 py-1.5 hover:bg-primary-50" onClick={onCancel}>
          Cancel
        </button>
        <button type="submit" disabled={save.isPending} className="rounded-md bg-primary-600 px-3 py-1.5 text-white hover:bg-primary-700 disabled:opacity-60">
          Save
        </button>
      </div>
    </form>
  )
}
