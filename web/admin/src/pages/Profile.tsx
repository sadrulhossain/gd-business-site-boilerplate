import { useMutation } from '@tanstack/react-query'
import { useState } from 'react'
import { ApiError } from '@gd/api-client'
import { api } from '../api'
import { useAuth } from '../auth/store'
import { FormField } from '../components/FormField'
import { toast } from '../components/Toast'
import { fieldErrors } from '../lib/crud'
import { checkPasswordChange, type PasswordForm } from '../lib/profile'
import type { Me } from '../types'

const emptyPassword: PasswordForm = { current_password: '', password: '', password_confirmation: '' }
const save = 'rounded-md bg-primary-600 px-3 py-1.5 text-white hover:bg-primary-700 disabled:opacity-60'

/** Own account: display name and password. Email is managed by an administrator. */
export function ProfilePage() {
  const user = useAuth((s) => s.user)
  const setUser = useAuth((s) => s.setUser)

  const update = (body: Record<string, unknown>) => api.request('PATCH', '/v1/me', { body }) as Promise<{ data: Me }>

  const [name, setName] = useState(user?.name ?? '')
  const [nameErrors, setNameErrors] = useState<Record<string, string>>({})
  const rename = useMutation({
    mutationFn: () => update({ name }),
    onSuccess: ({ data }) => {
      setUser(data)
      toast.success('Profile saved')
    },
    onError: (error) => {
      setNameErrors(fieldErrors(error))
      toast.error('Could not save the profile')
    },
  })

  const [pw, setPw] = useState(emptyPassword)
  const [pwErrors, setPwErrors] = useState<Record<string, string>>({})
  const change = useMutation({
    mutationFn: () => update({ ...pw }),
    onSuccess: () => {
      setPw(emptyPassword)
      toast.success('Password changed. Other sessions were signed out.')
    },
    onError: (error) => {
      setPwErrors(fieldErrors(error))
      toast.error(error instanceof ApiError && error.status === 422 ? 'Please fix the highlighted fields' : 'Could not change the password')
    },
  })
  const setField = (key: keyof PasswordForm) => (value: unknown) => setPw((p) => ({ ...p, [key]: String(value) }))

  return (
    <section className="max-w-xl space-y-8">
      <h1 className="text-2xl font-semibold">Profile</h1>

      <form
        className="space-y-3"
        noValidate
        onSubmit={(e) => {
          e.preventDefault()
          setNameErrors({})
          rename.mutate()
        }}
      >
        <h2 className="text-lg font-semibold">Details</h2>
        <FormField label="Name" name="name" required value={name} error={nameErrors.name} onChange={(v) => setName(String(v))} />
        <p className="text-sm text-ink-muted">Email: {user?.email}</p>
        <button type="submit" disabled={rename.isPending} className={save}>
          Save
        </button>
      </form>

      <form
        className="space-y-3"
        noValidate
        onSubmit={(e) => {
          e.preventDefault()
          const problems = checkPasswordChange(pw)
          setPwErrors(problems)
          if (Object.keys(problems).length === 0) change.mutate()
        }}
      >
        <h2 className="text-lg font-semibold">Password</h2>
        <FormField label="Current password" name="current_password" type="password" value={pw.current_password} error={pwErrors.current_password} onChange={setField('current_password')} />
        <FormField label="New password" name="password" type="password" hint="At least 12 characters." value={pw.password} error={pwErrors.password} onChange={setField('password')} />
        <FormField label="Confirm new password" name="password_confirmation" type="password" value={pw.password_confirmation} error={pwErrors.password_confirmation} onChange={setField('password_confirmation')} />
        <button type="submit" disabled={change.isPending} className={save}>
          Change password
        </button>
      </form>
    </section>
  )
}
