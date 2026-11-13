import { useState, type ReactNode } from 'react'

export interface Column<Row> {
  key: string
  header: string
  /** Defaults to the row's value at `key`. */
  render?: (row: Row) => ReactNode
}

export function DataTable<Row>({
  columns,
  rows,
  rowKey,
  loading = false,
  empty = 'Nothing here yet.',
  actions,
  onDropRow,
}: {
  columns: Column<Row>[]
  rows: Row[]
  rowKey: (row: Row) => string | number
  loading?: boolean
  empty?: string
  /** Rendered in a trailing cell of every row. */
  actions?: (row: Row) => ReactNode
  /** Makes rows draggable; called with the dragged row and the row it was dropped on. */
  onDropRow?: (from: Row, to: Row) => void
}) {
  const [dragging, setDragging] = useState<Row | null>(null)
  const span = columns.length + (actions ? 1 : 0)

  return (
    <div className="overflow-x-auto rounded-lg border border-line bg-surface">
      <table className="w-full text-left">
        <thead className="border-b border-line bg-canvas text-ink-muted">
          <tr>
            {columns.map((c) => (
              <th key={c.key} scope="col" className="px-3 py-2 font-semibold">
                {c.header}
              </th>
            ))}
            {actions && <th scope="col" className="px-3 py-2" />}
          </tr>
        </thead>
        <tbody>
          {loading && (
            <tr>
              <td colSpan={span} className="px-3 py-6 text-center text-ink-muted">
                Loading...
              </td>
            </tr>
          )}
          {!loading && rows.length === 0 && (
            <tr>
              <td colSpan={span} className="px-3 py-6 text-center text-ink-muted">
                {empty}
              </td>
            </tr>
          )}
          {!loading &&
            rows.map((row) => (
              <tr
                key={rowKey(row)}
                className={`border-b border-line last:border-0 ${onDropRow ? 'cursor-grab' : ''} ${dragging === row ? 'opacity-50' : ''}`}
                draggable={onDropRow ? true : undefined}
                onDragStart={onDropRow ? () => setDragging(row) : undefined}
                onDragEnd={onDropRow ? () => setDragging(null) : undefined}
                onDragOver={onDropRow ? (e) => e.preventDefault() : undefined}
                onDrop={
                  onDropRow
                    ? (e) => {
                        e.preventDefault()
                        if (dragging && dragging !== row) onDropRow(dragging, row)
                        setDragging(null)
                      }
                    : undefined
                }
              >
                {columns.map((c) => (
                  <td key={c.key} className="px-3 py-2">
                    {c.render ? c.render(row) : String((row as Record<string, unknown>)[c.key] ?? '')}
                  </td>
                ))}
                {actions && <td className="px-3 py-2 text-right whitespace-nowrap">{actions(row)}</td>}
              </tr>
            ))}
        </tbody>
      </table>
    </div>
  )
}
