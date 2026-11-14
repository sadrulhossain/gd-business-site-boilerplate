import { IconButton } from './IconButton'
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
      className="m-auto w-[min(32rem,calc(100vw-2rem))] rounded-2xl border border-line bg-surface p-4 text-ink sm:p-6 shadow-lg backdrop:bg-black/50 backdrop:backdrop-blur-sm max-h-[calc(100dvh-1.5rem)] overflow-y-auto"
    >
      <h2 className="mb-4 text-lg font-semibold tracking-tight">{title}</h2>
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
        <IconButton showText variant="secondary" icon="x" label="Cancel" onClick={onCancel} />
        <IconButton showText variant={danger ? 'danger' : 'primary'} icon={danger ? 'trash' : 'check'} label={confirmLabel} disabled={busy} onClick={onConfirm} />
      </div>
    </Modal>
  )
}
