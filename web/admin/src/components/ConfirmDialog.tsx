import { useEffect, useRef, type ReactNode } from 'react'

/** Modal built on <dialog>: focus trap, Escape and backdrop come from the browser. Shared by ConfirmDialog and CrudPage forms. */
export function Modal({ open, title, onClose, children }: { open: boolean; title: string; onClose: () => void; children: ReactNode }) {
  const ref = useRef<HTMLDialogElement>(null)

  useEffect(() => {
    const dialog = ref.current
    if (!dialog) return
    if (open && !dialog.open) dialog.showModal()
    if (!open && dialog.open) dialog.close()
  }, [open])

  return (
    <dialog
      ref={ref}
      aria-label={title}
      onClose={onClose}
      className="m-auto w-[min(32rem,calc(100vw-2rem))] rounded-lg border border-line bg-surface p-5 text-ink backdrop:bg-ink/40"
    >
      <h2 className="mb-3 text-lg font-semibold">{title}</h2>
      {open && children}
    </dialog>
  )
}

export function ConfirmDialog({
  open,
  title,
  message,
  confirmLabel = 'Confirm',
  danger = false,
  busy = false,
  onConfirm,
  onCancel,
}: {
  open: boolean
  title: string
  message: string
  confirmLabel?: string
  danger?: boolean
  busy?: boolean
  onConfirm: () => void
  onCancel: () => void
}) {
  return (
    <Modal open={open} title={title} onClose={onCancel}>
      <p className="mb-4 text-ink-muted">{message}</p>
      <div className="flex justify-end gap-2">
        <button type="button" className="rounded-md border border-line bg-surface px-3 py-1.5 hover:bg-primary-50" onClick={onCancel}>
          Cancel
        </button>
        <button
          type="button"
          disabled={busy}
          className={`rounded-md px-3 py-1.5 text-white disabled:opacity-60 ${danger ? 'bg-danger' : 'bg-primary-600 hover:bg-primary-700'}`}
          onClick={onConfirm}
        >
          {confirmLabel}
        </button>
      </div>
    </Modal>
  )
}
