import { useMutation, useQueryClient } from '@tanstack/react-query'
import { api } from '../api'
import { useAuth } from '../auth/store'
import { toast } from './Toast'

/**
 * A checkbox in a table cell that PATCHes one boolean field of a row straight away (`${endpoint}/${id}`).
 * Disabled without `${permissionKey}.update`. Use it from a column's `render`.
 */
export function ToggleCell({ endpoint, permissionKey, row, field, label }: { endpoint: string; permissionKey: string; row: { id: number } & Record<string, unknown>; field: string; label: string }) {
  const queryClient = useQueryClient()
  const allowed = useAuth((s) => s.can)(`${permissionKey}.update`)
  const toggle = useMutation({
    mutationFn: (value: boolean) => api.request('PATCH', `${endpoint}/${row.id}`, { body: { [field]: value } }),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['crud', endpoint] }),
    onError: () => toast.error('Could not save the change'),
  })

  return (
    <input
      type="checkbox"
      className="size-4"
      aria-label={label}
      checked={Boolean(row[field])}
      disabled={!allowed || toggle.isPending}
      onChange={(e) => toggle.mutate(e.target.checked)}
    />
  )
}
