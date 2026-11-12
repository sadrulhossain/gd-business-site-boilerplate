import { create } from 'zustand'
import type { Me } from '../types'

export interface TokenResponse {
  access_token: string
  refresh_token?: string
  /** Seconds. */
  expires_in: number
}

/**
 * Auth state, memory only: no persist middleware, nothing in localStorage or sessionStorage.
 * A reload starts empty and signs in again through the API's browser session (see oauth.ts).
 *
 * status: anonymous (no tokens) -> loading (tokens, user not fetched yet) -> authenticated.
 */
interface AuthState {
  status: 'anonymous' | 'loading' | 'authenticated'
  accessToken: string | null
  refreshToken: string | null
  /** Epoch ms. */
  expiresAt: number | null
  user: Me | null
  setSession: (tokens: TokenResponse) => void
  setUser: (user: Me) => void
  clear: () => void
  can: (permission: string) => boolean
}

export const useAuth = create<AuthState>((set, get) => ({
  status: 'anonymous',
  accessToken: null,
  refreshToken: null,
  expiresAt: null,
  user: null,

  setSession: (tokens) =>
    set({
      accessToken: tokens.access_token,
      // A refresh answer may omit the refresh token; keep the one we have.
      refreshToken: tokens.refresh_token ?? get().refreshToken,
      expiresAt: Date.now() + tokens.expires_in * 1000,
      status: get().user ? 'authenticated' : 'loading',
    }),

  setUser: (user) => set({ user, status: 'authenticated' }),

  clear: () => set({ status: 'anonymous', accessToken: null, refreshToken: null, expiresAt: null, user: null }),

  can: (permission) => get().user?.permissions.includes(permission) ?? false,
}))
