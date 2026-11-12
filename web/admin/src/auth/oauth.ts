import { config } from '../lib/config'
import { navigate } from '../lib/navigate'
import { challengeFor, randomString } from '../lib/pkce'
import type { Me } from '../types'
import { useAuth, type TokenResponse } from './store'

/** Authorization Code + PKCE (DECISIONS.md 005). Only the verifier and state touch storage, and only across the redirect. */
const PKCE_KEY = 'admin.pkce'

export class AuthError extends Error {}

/** Leaves for the API's login page; it sends the browser back to /auth/callback with a code. */
export async function startLogin(returnTo = '/') {
  const verifier = randomString(48)
  const state = randomString(16)
  sessionStorage.setItem(PKCE_KEY, JSON.stringify({ verifier, state, returnTo }))

  const params = new URLSearchParams({
    client_id: config.clientId,
    redirect_uri: config.redirectUri(),
    response_type: 'code',
    code_challenge: await challengeFor(verifier),
    code_challenge_method: 'S256',
    state,
  })
  navigate(`${config.apiOrigin()}/oauth/authorize?${params}`)
}

async function tokenRequest(body: Record<string, string>): Promise<Response> {
  return fetch(config.tokenUrl(), {
    method: 'POST',
    headers: { Accept: 'application/json', 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams({ client_id: config.clientId, ...body }),
  })
}

/** Exchanges the code in `search` (the callback URL's query string). Resolves to the path to return to. */
export async function completeLogin(search: string): Promise<string> {
  const query = new URLSearchParams(search)
  const saved = sessionStorage.getItem(PKCE_KEY)
  sessionStorage.removeItem(PKCE_KEY)

  if (query.get('error')) throw new AuthError(query.get('error_description') ?? 'Sign-in was refused.')
  if (!saved) throw new AuthError('No sign-in in progress. Start again.')

  const { verifier, state, returnTo } = JSON.parse(saved) as { verifier: string; state: string; returnTo: string }
  const code = query.get('code')
  if (!code || query.get('state') !== state) throw new AuthError('Sign-in response did not match the request.')

  const response = await tokenRequest({
    grant_type: 'authorization_code',
    redirect_uri: config.redirectUri(),
    code,
    code_verifier: verifier,
  })
  if (!response.ok) throw new AuthError('Could not complete sign-in.')

  useAuth.getState().setSession((await response.json()) as TokenResponse)

  return returnTo
}

/** Fetches /me and marks the session authenticated. */
export async function loadUser(): Promise<Me> {
  const token = await getValidToken()
  const response = await fetch(`${config.apiBase()}/v1/me`, {
    headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
  })
  if (!response.ok) throw new AuthError('Could not load your account.')

  const user = ((await response.json()) as { data: Me }).data
  useAuth.getState().setUser(user)

  return user
}

let inflight: Promise<boolean> | null = null

/**
 * Renews the access token with the refresh token. One request at a time: concurrent callers share it.
 * Resolves false when there is nothing to refresh with or the server refused (the session is then cleared,
 * so route guards send the user through sign-in again); a network error keeps the session and resolves false.
 */
export function refreshTokens(): Promise<boolean> {
  inflight ??= doRefresh().finally(() => {
    inflight = null
  })

  return inflight
}

async function doRefresh(): Promise<boolean> {
  const refreshToken = useAuth.getState().refreshToken
  if (!refreshToken) return false

  try {
    const response = await tokenRequest({ grant_type: 'refresh_token', refresh_token: refreshToken })
    if (!response.ok) {
      useAuth.getState().clear()
      return false
    }
    useAuth.getState().setSession((await response.json()) as TokenResponse)

    return true
  } catch {
    return false
  }
}

/** Current access token, renewed first when it expires within 30 seconds. */
export async function getValidToken(): Promise<string | null> {
  const { accessToken, expiresAt, refreshToken } = useAuth.getState()

  if (accessToken && refreshToken && expiresAt !== null && expiresAt - Date.now() < 30_000) {
    await refreshTokens()
  }

  return useAuth.getState().accessToken
}
