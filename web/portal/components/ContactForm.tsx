'use client'

import { useCallback, useEffect, useState, type FormEvent } from 'react'
import type { Messages } from '../lib/i18n'

type Status = 'idle' | 'sending' | 'success' | 'error'

interface Captcha {
  id: string
  question: string
}

const empty = { name: '', email: '', phone: '', subject: '', message: '', captcha_answer: '', website: '' }
const control = 'w-full rounded-md border border-line bg-surface px-4 py-3 shadow-sm focus:outline-2 focus:outline-brand-500'

/**
 * Contact form. The captcha comes from the API through /api/contact/captcha, one challenge per attempt (a new one is
 * fetched after every submit). `website` is the honeypot: hidden from people and assistive tech, bots fill it and the API refuses it.
 */
export function ContactForm({ messages }: { messages: Messages['contactPage'] }) {
  const [values, setValues] = useState(empty)
  const [captcha, setCaptcha] = useState<Captcha | null>(null)
  const [status, setStatus] = useState<Status>('idle')
  const [errors, setErrors] = useState<Record<string, string>>({})
  const [notice, setNotice] = useState('')

  const loadCaptcha = useCallback(async () => {
    setCaptcha(null)
    try {
      const response = await fetch('/api/contact/captcha', { cache: 'no-store' })
      setCaptcha(((await response.json()) as { data: Captcha }).data)
    } catch {
      setCaptcha(null)
    }
  }, [])

  useEffect(() => {
    // Fetched in the browser, not rendered on the server, so a cached page never ships a used-up challenge.
    // eslint-disable-next-line react-hooks/set-state-in-effect
    void loadCaptcha()
  }, [loadCaptcha])

  const set = (name: keyof typeof empty) => (value: string) => setValues((v) => ({ ...v, [name]: value }))

  async function submit(event: FormEvent) {
    event.preventDefault()
    if (!captcha) return
    setStatus('sending')
    setErrors({})
    setNotice('')

    try {
      const response = await fetch('/api/contact', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
        body: JSON.stringify({
          name: values.name,
          email: values.email,
          phone: values.phone || null,
          subject: values.subject || null,
          message: values.message,
          captcha_id: captcha.id,
          captcha_answer: Number.parseInt(values.captcha_answer, 10),
          website: values.website || undefined,
        }),
      })

      if (response.ok) {
        setStatus('success')
        setValues(empty)
        return
      }

      const body = (await response.json().catch(() => ({}))) as { error?: { details?: Record<string, string[]> } }
      const details = body.error?.details ?? {}
      setErrors(Object.fromEntries(Object.entries(details).map(([field, list]) => [field, list[0] ?? ''])))
      setNotice(response.status === 429 ? messages.errorThrottle : details.captcha_answer || details.captcha_id ? messages.errorCaptcha : response.status === 422 ? messages.errorFields : messages.errorGeneric)
      setStatus('error')
    } catch {
      setNotice(messages.errorGeneric)
      setStatus('error')
    }

    // The challenge is single-use whatever happened.
    setValues((v) => ({ ...v, captcha_answer: '' }))
    void loadCaptcha()
  }

  if (status === 'success') {
    return (
      <div role="status" className="space-y-4 rounded-md border border-success bg-surface p-5">
        <p className="text-success">{messages.success}</p>
        <button
          type="button"
          className="rounded-md border border-line px-4 py-2 hover:bg-brand-50"
          onClick={() => {
            setStatus('idle')
            void loadCaptcha()
          }}
        >
          {messages.another}
        </button>
      </div>
    )
  }

  const field = (name: 'name' | 'email' | 'phone' | 'subject', label: string, type = 'text', autoComplete?: string) => (
    <div className="space-y-1">
      <label htmlFor={`contact-${name}`} className="block text-sm text-ink-muted">
        {label}
      </label>
      <input
        id={`contact-${name}`}
        name={name}
        type={type}
        autoComplete={autoComplete}
        required={name === 'name' || name === 'email'}
        aria-invalid={errors[name] ? true : undefined}
        aria-describedby={errors[name] ? `contact-${name}-error` : undefined}
        className={control}
        value={values[name]}
        onChange={(e) => set(name)(e.target.value)}
      />
      {errors[name] && (
        <p id={`contact-${name}-error`} className="text-sm text-danger">
          {errors[name]}
        </p>
      )}
    </div>
  )

  return (
    <form onSubmit={submit} noValidate className="space-y-4" aria-describedby={notice ? 'contact-notice' : undefined}>
      {notice && (
        <p id="contact-notice" role="alert" className="rounded-md border border-danger bg-surface p-3 text-danger">
          {notice}
        </p>
      )}

      {field('name', messages.name, 'text', 'name')}
      {field('email', messages.email, 'email', 'email')}
      {field('phone', messages.phone, 'tel', 'tel')}
      {field('subject', messages.subject)}

      <div className="space-y-1">
        <label htmlFor="contact-message" className="block text-sm text-ink-muted">
          {messages.message}
        </label>
        <textarea
          id="contact-message"
          name="message"
          rows={6}
          required
          aria-invalid={errors.message ? true : undefined}
          aria-describedby={errors.message ? 'contact-message-error' : undefined}
          className={control}
          value={values.message}
          onChange={(e) => set('message')(e.target.value)}
        />
        {errors.message && (
          <p id="contact-message-error" className="text-sm text-danger">
            {errors.message}
          </p>
        )}
      </div>

      {/* Honeypot: off-screen (not display:none, which some bots skip), not focusable, not announced. */}
      <div aria-hidden="true" className="absolute -start-[9999px] h-0 w-0 overflow-hidden">
        <label htmlFor="contact-website">Website</label>
        <input id="contact-website" name="website" type="text" tabIndex={-1} autoComplete="off" value={values.website} onChange={(e) => set('website')(e.target.value)} />
      </div>

      <div className="space-y-1">
        <label htmlFor="contact-captcha" className="block text-sm text-ink-muted">
          {captcha ? messages.captcha.replace('{question}', captcha.question) : messages.captchaLoading}
        </label>
        <input
          id="contact-captcha"
          name="captcha_answer"
          type="text"
          inputMode="numeric"
          autoComplete="off"
          required
          disabled={!captcha}
          aria-invalid={errors.captcha_answer ? true : undefined}
          className={`${control} max-w-40`}
          value={values.captcha_answer}
          onChange={(e) => set('captcha_answer')(e.target.value)}
        />
      </div>

      <button type="submit" disabled={status === 'sending' || !captcha} className="rounded-full bg-brand-600 px-7 py-3 font-semibold text-white shadow-soft transition hover:bg-brand-700 disabled:opacity-60">
        {status === 'sending' ? messages.sending : messages.send}
      </button>
    </form>
  )
}
