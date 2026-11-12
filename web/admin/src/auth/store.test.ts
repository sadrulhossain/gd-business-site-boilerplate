import { beforeEach, describe, expect, it, vi } from 'vitest'
import { config } from '../lib/config'
import { navigate } from '../lib/navigate'
import { completeLogin, getValidToken, refreshTokens, startLogin } from './oauth'
import { useAuth } from './store'

vi.mock('../lib/navigate', () => ({ navigate: vi.fn() }))

const me = { id: 1, name: 'A', email: 'a@example.test', is_superadmin: false, roles: [], permissions: ['faqs.view'] }
const tokens = { access_token: 'access-1', refresh_token: 'refresh-1', expires_in: 3600 }

const json = (body: unknown, status = 200) =>
  new Response(JSON.stringify(body), { status, headers: { 'Content-Type': 'application/json' } })

const sentBody = (fetchMock: { mock: { calls: unknown[][] } }) =>
  new URLSearchParams((fetchMock.mock.calls[0][1] as RequestInit).body as URLSearchParams)

beforeEach(() => {
  useAuth.getState().clear()
  sessionStorage.clear()
  localStorage.clear()
})

describe('auth store', () => {
  it('starts anonymous and becomes authenticated only once the user is loaded', () => {
    expect(useAuth.getState().status).toBe('anonymous')

    useAuth.getState().setSession(tokens)
    expect(useAuth.getState().status).toBe('loading')

    useAuth.getState().setUser(me as never)
    expect(useAuth.getState().status).toBe('authenticated')
  })

  it('keeps tokens in memory only', () => {
    useAuth.getState().setSession(tokens)
    useAuth.getState().setUser(me as never)

    const stored = JSON.stringify({ ...localStorage }) + JSON.stringify({ ...sessionStorage })
    expect(stored).not.toContain('access-1')
    expect(stored).not.toContain('refresh-1')
  })

  it('answers can() from the loaded permissions', () => {
    expect(useAuth.getState().can('faqs.view')).toBe(false)

    useAuth.getState().setUser(me as never)
    expect(useAuth.getState().can('faqs.view')).toBe(true)
    expect(useAuth.getState().can('faqs.delete')).toBe(false)
  })

  it('clear() drops everything', () => {
    useAuth.getState().setSession(tokens)
    useAuth.getState().setUser(me as never)
    useAuth.getState().clear()

    expect(useAuth.getState()).toMatchObject({ status: 'anonymous', accessToken: null, refreshToken: null, user: null })
  })

  it('keeps the old refresh token when a refresh answer omits one', () => {
    useAuth.getState().setSession(tokens)
    useAuth.getState().setSession({ access_token: 'access-2', expires_in: 3600 })

    expect(useAuth.getState()).toMatchObject({ accessToken: 'access-2', refreshToken: 'refresh-1' })
  })
})

describe('token refresh', () => {
  it('exchanges the refresh token and stores the new tokens', async () => {
    useAuth.getState().setSession(tokens)
    const fetchMock = vi
      .spyOn(globalThis, 'fetch')
      .mockResolvedValue(json({ access_token: 'access-2', refresh_token: 'refresh-2', expires_in: 3600 }))

    expect(await refreshTokens()).toBe(true)

    expect(useAuth.getState()).toMatchObject({ accessToken: 'access-2', refreshToken: 'refresh-2' })
    const body = sentBody(fetchMock)
    expect(body.get('grant_type')).toBe('refresh_token')
    expect(body.get('refresh_token')).toBe('refresh-1')
    expect(body.has('client_secret')).toBe(false)
  })

  it('shares one request between concurrent callers', async () => {
    useAuth.getState().setSession(tokens)
    const fetchMock = vi
      .spyOn(globalThis, 'fetch')
      .mockResolvedValue(json({ access_token: 'access-2', refresh_token: 'refresh-2', expires_in: 3600 }))

    const results = await Promise.all([refreshTokens(), refreshTokens(), refreshTokens()])

    expect(results).toEqual([true, true, true])
    expect(fetchMock).toHaveBeenCalledTimes(1)
  })

  it('clears the session when the server refuses the refresh token', async () => {
    useAuth.getState().setSession(tokens)
    vi.spyOn(globalThis, 'fetch').mockResolvedValue(json({ error: 'invalid_grant' }, 400))

    expect(await refreshTokens()).toBe(false)
    expect(useAuth.getState().status).toBe('anonymous')
  })

  it('keeps the session on a network error', async () => {
    useAuth.getState().setSession(tokens)
    vi.spyOn(globalThis, 'fetch').mockRejectedValue(new TypeError('offline'))

    expect(await refreshTokens()).toBe(false)
    expect(useAuth.getState().accessToken).toBe('access-1')
  })

  it('does nothing without a refresh token', async () => {
    const fetchMock = vi.spyOn(globalThis, 'fetch')

    expect(await refreshTokens()).toBe(false)
    expect(fetchMock).not.toHaveBeenCalled()
  })

  it('leaves a fresh token alone and renews one that expires within 30 seconds', async () => {
    const fetchMock = vi
      .spyOn(globalThis, 'fetch')
      .mockResolvedValue(json({ access_token: 'access-2', refresh_token: 'refresh-2', expires_in: 3600 }))

    useAuth.getState().setSession(tokens)
    expect(await getValidToken()).toBe('access-1')
    expect(fetchMock).not.toHaveBeenCalled()

    useAuth.getState().setSession({ ...tokens, expires_in: 10 })
    expect(await getValidToken()).toBe('access-2')
    expect(fetchMock).toHaveBeenCalledTimes(1)
  })
})

describe('sign-in', () => {
  it('startLogin leaves for the authorize URL with an S256 challenge and stores only verifier and state', async () => {
    await startLogin('/faqs')

    const url = new URL(vi.mocked(navigate).mock.calls[0][0])
    expect(url.pathname).toBe('/oauth/authorize')
    expect(url.searchParams.get('response_type')).toBe('code')
    expect(url.searchParams.get('code_challenge_method')).toBe('S256')
    expect(url.searchParams.get('code_challenge')).toMatch(/^[A-Za-z0-9_-]{43}$/)
    expect(url.searchParams.get('client_id')).toBe(config.clientId)
    expect(url.searchParams.get('redirect_uri')).toBe(`${location.origin}/auth/callback`)

    const saved = JSON.parse(sessionStorage.getItem('admin.pkce') ?? '{}')
    expect(saved.returnTo).toBe('/faqs')
    expect(saved.state).toBe(url.searchParams.get('state'))
    expect(saved.verifier).not.toBe(url.searchParams.get('code_challenge'))
  })

  it('completeLogin exchanges the code with the verifier and resolves to the return path', async () => {
    sessionStorage.setItem('admin.pkce', JSON.stringify({ verifier: 'v', state: 's', returnTo: '/faqs' }))
    const fetchMock = vi.spyOn(globalThis, 'fetch').mockResolvedValue(json(tokens))

    expect(await completeLogin('?code=abc&state=s')).toBe('/faqs')

    const body = sentBody(fetchMock)
    expect(body.get('grant_type')).toBe('authorization_code')
    expect(body.get('code_verifier')).toBe('v')
    expect(body.get('code')).toBe('abc')
    expect(useAuth.getState().accessToken).toBe('access-1')
    expect(sessionStorage.getItem('admin.pkce')).toBeNull()
  })

  it('completeLogin rejects a state mismatch without calling the token endpoint', async () => {
    sessionStorage.setItem('admin.pkce', JSON.stringify({ verifier: 'v', state: 's', returnTo: '/' }))
    const fetchMock = vi.spyOn(globalThis, 'fetch')

    await expect(completeLogin('?code=abc&state=other')).rejects.toThrow()
    expect(fetchMock).not.toHaveBeenCalled()
    expect(useAuth.getState().status).toBe('anonymous')
  })

  it('completeLogin rejects when no sign-in was started or the server returned an error', async () => {
    await expect(completeLogin('?code=abc&state=s')).rejects.toThrow('No sign-in in progress')

    sessionStorage.setItem('admin.pkce', JSON.stringify({ verifier: 'v', state: 's', returnTo: '/' }))
    await expect(completeLogin('?error=access_denied')).rejects.toThrow()
  })
})
