import { keepPreviousData, useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { useState, type ReactNode } from 'react'
import { ApiError } from '@gd/api-client'
import { api } from '../api'
import { useAuth } from '../auth/store'
import { blank, fieldErrors, payload } from '../lib/crud'
import { moveId } from '../lib/reorder'
import type { Listing } from '../types'
import { BilingualField } from './BilingualField'
import { ConfirmDialog, Modal } from './ConfirmDialog'
import { ImagePanel } from './ImagePanel'
import { DataTable, type Column } from './DataTable'
import { FormField, type FieldOption, type FieldType } from './FormField'
import { toast } from './Toast'
import { IconButton } from './IconButton'

export type Row = Record<string, unknown> & { id: number }

export interface CrudField {
  name: string
  label: string
  type?: FieldType
  /** Renders `name` and `name_2nd`, labelled with the locale codes. Text, textarea and richtext only. */
  bilingual?: boolean
  required?: boolean
  hint?: string
  options?: FieldOption[]
  /** Options fetched from the API instead (roles for a user, permissions for a role); `map` turns one item of `data` into an option. */
  optionsFrom?: { endpoint: string; map: (item: never) => FieldOption }
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
  /**
   * Rows become draggable. Dropping sends the new order of the rows sharing `group` (all rows when omitted) as
   * `PUT endpoint {ids}`. Needs `${key}.update`.
   */
  /** The rows have an image at `${endpoint}/${id}/image` (POST to replace, DELETE to remove); shown in the edit form. */
  image?: boolean
  reorder?: { endpoint: string; group?: (row: Row) => unknown }
}

/** Forms with rich text or many fields get a wider dialog. */
function formSize(fields: CrudField[]): 'md' | 'lg' | 'xl' {
  if (fields.some((f) => f.type === 'richtext' || f.type === 'checkgroup')) return 'xl'

  return fields.length > 4 ? 'lg' : 'md'
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

  const move = useMutation({
    mutationFn: (ids: number[]) => api.request('PUT', config.reorder!.endpoint, { body: { ids } }),
    onSuccess: () => void refresh(),
    onError: () => toast.error('Could not save the new order'),
  })

  const onDropRow = (from: Row, to: Row) => {
    const group = config.reorder?.group
    if (group && group(from) !== group(to)) return

    const siblings = group ? rows.filter((r) => group(r) === group(from)) : rows
    const ids = siblings.map((r) => r.id)
    const next = moveId(ids, from.id, to.id)
    if (next !== ids) move.mutate(next)
  }

  const rows = list.data?.data ?? []
  // Menu, role and user edits can change the signed-in user's sidebar, so it reloads after every write.
  const refresh = () => Promise.all([queryClient.invalidateQueries({ queryKey: ['crud', config.endpoint] }), queryClient.invalidateQueries({ queryKey: ['menus'] })])

  const remove = useMutation({
    mutationFn: (row: Row) => api.request('DELETE', `${config.endpoint}/${row.id}`),
    onSuccess: () => {
      toast.success(`${config.noun} deleted`)
      setDeleting(null)
      void refresh()
    },
    onError: () => toast.error(`Could not delete the ${config.noun.toLowerCase()}`),
  })

  const meta = list.data?.meta
  const actions = (row: Row): ReactNode => (
    <span className="inline-flex gap-2">
      {canUpdate && (
        <IconButton variant="row" icon="pencil" label="Edit" onClick={() => setEditing(row)} />
      )}
      {canDelete && (
        <IconButton variant="row-danger" icon="trash" label="Delete" onClick={() => setDeleting(row)} />
      )}
    </span>
  )

  return (
    <section className="space-y-4">
      <header className="flex flex-wrap items-center justify-between gap-2">
        <h1 className="text-2xl font-semibold">{config.title}</h1>
        {canCreate && (
          <IconButton showText variant="primary" icon="plus" label={`New ${config.noun.toLowerCase()}`} onClick={() => setEditing('new')} />
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
          onDropRow={config.reorder && canUpdate ? onDropRow : undefined}
        />
      )}

      {meta && meta.last_page > 1 && (
        <nav className="flex items-center justify-end gap-3" aria-label="Pagination">
          <IconButton variant="secondary" icon="chevron-left" label="Previous" disabled={page <= 1} onClick={() => setPage(page - 1)} />
          <span className="text-ink-muted">
            {meta.current_page} / {meta.last_page}
          </span>
          <IconButton variant="secondary" icon="chevron-right" label="Next" disabled={page >= meta.last_page} onClick={() => setPage(page + 1)} />
        </nav>
      )}

      <Modal size={formSize(config.fields)} open={editing !== null} title={editing === 'new' ? `New ${config.noun.toLowerCase()}` : `Edit ${config.noun.toLowerCase()}`} onClose={() => setEditing(null)}>
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
  const queryClient = useQueryClient()
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
            type={f.type === 'textarea' || f.type === 'richtext' ? f.type : 'text'}
            required={f.required}
            values={values}
            errors={errors}
            onChange={set}
          />
        ) : (
          <OptionsField key={f.name} field={f} value={values[f.name]} error={errors[f.name]} onChange={(v) => set(f.name, v)} />
        ),
      )}
      {config.image &&
        (row ? (
          <ImagePanel endpoint={`${config.endpoint}/${row.id}/image`} image={row.image as { url: string } | null | undefined} onChange={() => void queryClient.invalidateQueries({ queryKey: ['crud', config.endpoint] })} />
        ) : (
          <p className="text-sm text-ink-muted">Save first, then add an image.</p>
        ))}
      <div className="flex justify-end gap-2 pt-2">
        <IconButton showText variant="secondary" icon="x" label="Cancel" onClick={onCancel} />
        <IconButton showText type="submit" variant="primary" icon="save" label="Save" disabled={save.isPending} />
      </div>
    </form>
  )
}

/** A plain field whose options may come from the API. */
function OptionsField({ field, value, error, onChange }: { field: CrudField; value: unknown; error?: string; onChange: (value: unknown) => void }) {
  const remote = field.optionsFrom
  const options = useQuery({
    queryKey: ['options', remote?.endpoint],
    enabled: remote !== undefined,
    staleTime: 60_000,
    queryFn: async () => ((await api.request('GET', remote!.endpoint)) as { data: never[] }).data.map(remote!.map),
  })

  return (
    <FormField
      label={field.label}
      name={field.name}
      type={field.type}
      required={field.required}
      hint={field.hint}
      options={remote ? options.data : field.options}
      value={value}
      error={error}
      onChange={onChange}
    />
  )
}
