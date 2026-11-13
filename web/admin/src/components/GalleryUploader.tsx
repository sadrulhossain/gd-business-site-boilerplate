import { useQueryClient } from '@tanstack/react-query'
import { useRef, useState } from 'react'
import { ApiError } from '@gd/api-client'
import { api } from '../api'
import { useAuth } from '../auth/store'
import { checkImage, runPool, uploadFile } from '../lib/upload'

type Status = 'queued' | 'uploading' | 'done' | 'error'
interface Entry {
  id: number
  name: string
  status: Status
  progress: number
  error?: string
}

const LIMIT = 3

/**
 * Pick many images at once: each becomes a gallery item (create, then upload its image), up to three at a time, with a
 * progress bar per file. A file whose upload fails leaves no empty item behind. Titles/captions are edited afterwards.
 */
export function GalleryUploader({ endpoint }: { endpoint: string }) {
  const queryClient = useQueryClient()
  const can = useAuth((s) => s.can)
  const input = useRef<HTMLInputElement>(null)
  const [entries, setEntries] = useState<Entry[]>([])
  const [busy, setBusy] = useState(false)

  if (!can('gallery.create') || !can('gallery.update')) return null

  const patch = (id: number, change: Partial<Entry>) => setEntries((all) => all.map((e) => (e.id === id ? { ...e, ...change } : e)))

  const start = async (files: File[]) => {
    const base = Date.now()
    const jobs = files.map((file, i) => ({ file, id: base + i }))
    setEntries(jobs.map(({ file, id }) => ({ id, name: file.name, status: 'queued', progress: 0 })))
    setBusy(true)

    await runPool(jobs, LIMIT, async ({ file, id }) => {
      const problem = checkImage(file)
      if (problem) return patch(id, { status: 'error', error: problem })

      patch(id, { status: 'uploading' })
      let created: number | null = null
      try {
        created = ((await api.request('POST', endpoint, { body: {} })) as { data: { id: number } }).data.id
        await uploadFile(`${endpoint}/${created}/image`, file, (progress) => patch(id, { progress }))
        patch(id, { status: 'done', progress: 1 })
      } catch (error) {
        if (created !== null) await api.request('DELETE', `${endpoint}/${created}`).catch(() => undefined)
        const message = error instanceof ApiError && error.status === 422 ? (Object.values(error.details ?? {})[0]?.[0] ?? error.message) : 'Upload failed'
        patch(id, { status: 'error', error: message })
      }
    })

    setBusy(false)
    if (input.current) input.current.value = ''
    void queryClient.invalidateQueries({ queryKey: ['crud', endpoint] })
  }

  return (
    <div className="space-y-3 rounded-lg border border-line bg-surface p-4">
      <label className="block text-sm text-ink-muted" htmlFor="gallery-files">
        Add images (JPEG, PNG or WebP)
      </label>
      <input
        id="gallery-files"
        ref={input}
        type="file"
        multiple
        accept="image/jpeg,image/png,image/webp"
        disabled={busy}
        onChange={(e) => e.target.files && e.target.files.length > 0 && void start([...e.target.files])}
      />
      {entries.length > 0 && (
        <ul className="space-y-2" aria-label="Uploads">
          {entries.map((e) => (
            <li key={e.id} className="space-y-1">
              <div className="flex justify-between text-sm">
                <span className="truncate">{e.name}</span>
                <span className={e.status === 'error' ? 'text-danger' : 'text-ink-muted'}>{e.status === 'error' ? e.error : e.status}</span>
              </div>
              <progress className="w-full" max={1} value={e.progress} aria-label={`Progress ${e.name}`} />
            </li>
          ))}
        </ul>
      )}
    </div>
  )
}
