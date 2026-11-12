import { cleanup, render, screen } from '@testing-library/react'
import type { ReactNode } from 'react'
import { MemoryRouter } from 'react-router'
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'
import { RequireAuth, RequirePermission } from './guards'
import { startLogin } from './oauth'
import { useAuth } from './store'

vi.mock('./oauth', () => ({ startLogin: vi.fn() }))

const me = (permissions: string[]) =>
  ({ id: 1, name: 'A', email: 'a@example.test', is_superadmin: false, roles: [], permissions }) as never
const tokens = { access_token: 'a', refresh_token: 'r', expires_in: 3600 }

const at = (path: string, ui: ReactNode) => render(<MemoryRouter initialEntries={[path]}>{ui}</MemoryRouter>)

beforeEach(() => {
  useAuth.getState().clear()
  vi.mocked(startLogin).mockClear()
})
afterEach(cleanup)

describe('RequireAuth', () => {
  it('starts sign-in for a visitor without a session and hides the page', () => {
    at('/faqs?page=2', <RequireAuth>secret page</RequireAuth>)

    expect(startLogin).toHaveBeenCalledWith('/faqs?page=2')
    expect(screen.queryByText('secret page')).toBeNull()
    expect(screen.getByRole('status').textContent).toContain('Signing in')
  })

  it('waits, without redirecting again, while the account is loading', () => {
    useAuth.getState().setSession(tokens)
    at('/', <RequireAuth>secret page</RequireAuth>)

    expect(startLogin).not.toHaveBeenCalled()
    expect(screen.queryByText('secret page')).toBeNull()
  })

  it('renders the page for a signed-in user', () => {
    useAuth.getState().setSession(tokens)
    useAuth.getState().setUser(me([]))
    at('/', <RequireAuth>secret page</RequireAuth>)

    expect(screen.getByText('secret page')).toBeTruthy()
    expect(startLogin).not.toHaveBeenCalled()
  })
})

describe('RequirePermission', () => {
  it('renders the page when the user holds the permission', () => {
    useAuth.getState().setUser(me(['faqs.view']))
    at('/faqs', <RequirePermission permission="faqs.view">faq page</RequirePermission>)

    expect(screen.getByText('faq page')).toBeTruthy()
  })

  it('shows the 403 page when the permission is missing', () => {
    useAuth.getState().setUser(me(['posts.view']))
    at('/faqs', <RequirePermission permission="faqs.view">faq page</RequirePermission>)

    expect(screen.queryByText('faq page')).toBeNull()
    expect(screen.getByRole('heading', { name: 'Not allowed' })).toBeTruthy()
    expect(screen.getByText('403')).toBeTruthy()
  })

  it('shows the 403 page while no user is loaded', () => {
    at('/faqs', <RequirePermission permission="faqs.view">faq page</RequirePermission>)

    expect(screen.queryByText('faq page')).toBeNull()
  })
})
