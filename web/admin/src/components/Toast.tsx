import { create } from 'zustand'

interface ToastItem {
  id: number
  kind: 'success' | 'error'
  message: string
}

interface ToastState {
  toasts: ToastItem[]
  push: (kind: ToastItem['kind'], message: string) => void
  dismiss: (id: number) => void
}

let nextId = 1

export const useToasts = create<ToastState>((set, get) => ({
  toasts: [],
  push: (kind, message) => {
    const id = nextId++
    set({ toasts: [...get().toasts, { id, kind, message }] })
    setTimeout(() => get().dismiss(id), 4000)
  },
  dismiss: (id) => set({ toasts: get().toasts.filter((t) => t.id !== id) }),
}))

/** Imperative API: toast.success('Saved'). Render <ToastHost /> once, in the shell. */
export const toast = {
  success: (message: string) => useToasts.getState().push('success', message),
  error: (message: string) => useToasts.getState().push('error', message),
}

const styles = {
  success: 'border-s-4 border-s-success',
  error: 'border-s-4 border-s-danger',
} as const

export function ToastHost() {
  const toasts = useToasts((s) => s.toasts)
  const dismiss = useToasts((s) => s.dismiss)

  return (
    <div className="fixed right-4 bottom-4 z-50 flex w-80 flex-col gap-2" aria-live="polite">
      {toasts.map((t) => (
        <div
          key={t.id}
          role={t.kind === 'error' ? 'alert' : 'status'}
          className={`flex items-start justify-between gap-3 rounded-lg border bg-surface px-3 py-2.5 text-ink shadow-lg ${styles[t.kind]}`}
        >
          <span>{t.message}</span>
          <button type="button" aria-label="Dismiss" className="text-ink-muted" onClick={() => dismiss(t.id)}>
            x
          </button>
        </div>
      ))}
    </div>
  )
}
