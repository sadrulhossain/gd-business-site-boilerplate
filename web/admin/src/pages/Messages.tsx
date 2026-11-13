import { keepPreviousData, useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { useState } from 'react'
import { api } from '../api'
import { useAuth } from '../auth/store'
import { Modal } from '../components/ConfirmDialog'
import { DataTable } from '../components/DataTable'
import { toast } from '../components/Toast'
import type { Message, InboxConfig } from '../messagesConfig'
import type { Listing } from '../types'

const when = (value: string) => new Date(value).toLocaleString()

export function MessagesPage({ config }: { config: InboxConfig }) {
  const queryClient = useQueryClient()
  const canMark = useAuth((s) => s.can)(`${config.key}.update`)
  const [page, setPage] = useState(1)
  const [unreadOnly, setUnreadOnly] = useState(false)
  const [open, setOpen] = useState<Message | null>(null)

  const list = useQuery({
    queryKey: ['crud', config.endpoint, page, unreadOnly],
    placeholderData: keepPreviousData,
    queryFn: async () => (await api.request('GET', config.endpoint, { query: { page, unread: unreadOnly ? 1 : undefined } })) as Listing<Message>,
  })

  const markRead = useMutation({
    mutationFn: (id: number) => api.request('POST', config.readEndpoint(id)) as Promise<{ data: Message }>,
    onSuccess: ({ data }) => {
      setOpen(data)
      void queryClient.invalidateQueries({ queryKey: ['crud', config.endpoint] })
      void queryClient.invalidateQueries({ queryKey: ['dashboard'] })
    },
    onError: () => toast.error('Could not mark the message as read'),
  })

  const meta = list.data?.meta

  return (
    <section className="space-y-4">
      <header className="flex items-center justify-between">
        <h1 className="text-2xl font-semibold">{config.title}</h1>
        <label className="flex items-center gap-2">
          <input
            type="checkbox"
            className="size-4"
            checked={unreadOnly}
            onChange={(e) => {
              setUnreadOnly(e.target.checked)
              setPage(1)
            }}
          />
          Unread only
        </label>
      </header>

      {list.isError ? (
        <p role="alert" className="text-danger">
          Could not load {config.title.toLowerCase()}.
        </p>
      ) : (
        <DataTable
          columns={[
            { key: 'read_at', header: 'Status', render: (m: Message) => (m.read_at ? 'Read' : <strong>New</strong>) },
            { key: 'name', header: 'From' },
            { key: 'subject', header: 'Subject' },
            { key: 'created_at', header: 'Received', render: (m: Message) => when(m.created_at) },
          ]}
          rows={list.data?.data ?? []}
          rowKey={(m) => m.id}
          loading={list.isPending}
          empty="No messages."
          actions={(m) => (
            <button type="button" className="text-primary-600 hover:underline" onClick={() => setOpen(m)}>
              Open
            </button>
          )}
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

      <Modal open={open !== null} title="Message" onClose={() => setOpen(null)}>
        {open && (
          <div className="space-y-3">
            <dl className="space-y-2">
              {config.fields.map((f) => {
                const value = open[f.name as keyof Message]

                return (
                  <div key={f.name}>
                    <dt className="text-sm text-ink-muted">{f.label}</dt>
                    <dd className="whitespace-pre-wrap break-words">{f.name === 'created_at' && value ? when(String(value)) : String(value ?? '-')}</dd>
                  </div>
                )
              })}
            </dl>
            <div className="flex justify-end gap-2 pt-2">
              {canMark && !open.read_at && (
                <button type="button" disabled={markRead.isPending} className="rounded-md bg-primary-600 px-3 py-1.5 text-white hover:bg-primary-700 disabled:opacity-60" onClick={() => markRead.mutate(open.id)}>
                  Mark as read
                </button>
              )}
              <button type="button" className="rounded-md border border-line bg-surface px-3 py-1.5 hover:bg-primary-50" onClick={() => setOpen(null)}>
                Close
              </button>
            </div>
          </div>
        )}
      </Modal>
    </section>
  )
}
