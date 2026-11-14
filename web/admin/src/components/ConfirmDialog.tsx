import { IconButton } from './IconButton'
import { useEffect, useRef, type MouseEvent, type ReactNode } from 'react'

/** Modal built on <dialog>: focus trap, Escape and backdrop come from the browser. Shared by ConfirmDialog and CrudPage forms. */
const WIDTHS = { md: 'w-[min(32rem,calc(100vw-2rem))]', lg: 'w-[min(44rem,calc(100vw-2rem))]', xl: 'w-[min(60rem,calc(100vw-2rem))]' } as const

export function Modal({ open, title, onClose, children, size = 'md' }: { open: boolean; title: string; onClose: () => void; children: ReactNode; size?: keyof typeof WIDTHS }) {
  const ref = useRef<HTMLDialogElement>(null)
  // A press that starts and ends on the backdrop closes the dialog; dragging a text selection out of it does not.
  const pressedOutside = useRef(false)
  const outside = (e: MouseEvent<HTMLDialogElement>) => {
    const r = e.currentTarget.getBoundingClientRect()

    return e.clientX < r.left || e.clientX > r.right || e.clientY < r.top || e.clientY > r.bottom
  }

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
      onMouseDown={(e) => {
        pressedOutside.current = outside(e)
      }}
      onClick={(e) => {
        if (pressedOutside.current && outside(e)) onClose()
        pressedOutside.current = false
      }}
      className={`m-auto ${WIDTHS[size]} rounded-2xl border border-line bg-surface p-4 text-ink sm:p-6 shadow-lg backdrop:bg-black/50 backdrop:backdrop-blur-sm max-h-[calc(100dvh-1.5rem)] overflow-y-auto`}
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
