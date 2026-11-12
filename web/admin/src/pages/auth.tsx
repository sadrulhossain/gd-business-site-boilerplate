import { useEffect, useRef, useState } from 'react'
import { useNavigate } from 'react-router'
import { completeLogin, loadUser, startLogin } from '../auth/oauth'

/** OAuth redirect target: trades the code for tokens, loads the account, then goes where sign-in started. */
export function Callback() {
  const navigate = useNavigate()
  const [error, setError] = useState<string | null>(null)
  const started = useRef(false) // StrictMode runs effects twice; a code can only be exchanged once

  useEffect(() => {
    if (started.current) return
    started.current = true

    completeLogin(window.location.search)
      .then(async (returnTo) => {
        await loadUser()
        navigate(returnTo, { replace: true })
      })
      .catch((e: unknown) => setError(e instanceof Error ? e.message : 'Sign-in failed.'))
  }, [navigate])

  if (!error) {
    return (
      <p role="status" className="p-6 text-ink-muted">
        Signing in...
      </p>
    )
  }

  return (
    <section className="mx-auto max-w-sm space-y-3 p-6 text-center">
      <p role="alert" className="text-danger">
        {error}
      </p>
      <button type="button" className="rounded-md bg-primary-600 px-3 py-1.5 text-white hover:bg-primary-700" onClick={() => void startLogin('/')}>
        Try again
      </button>
    </section>
  )
}

/** Shown after /logout on the API. It never signs in on its own, or sign-out would loop. */
export function SignedOut() {
  return (
    <section className="mx-auto max-w-sm space-y-3 p-6 text-center">
      <h1 className="text-2xl font-semibold">Signed out</h1>
      <button type="button" className="rounded-md bg-primary-600 px-3 py-1.5 text-white hover:bg-primary-700" onClick={() => void startLogin('/')}>
        Sign in
      </button>
    </section>
  )
}
