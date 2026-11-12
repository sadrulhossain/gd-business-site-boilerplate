// Compile-time checks only (run by `pnpm typecheck`); never imported, never executed.
import { createClient } from './index'

export async function typeChecks() {
  const api = createClient({ baseUrl: 'http://api.example.test/api' })

  // Inferred: path params, body and response come from the spec.
  const health = await api.get('/v1/health')
  void health

  const faq = await api.get('/v1/faqs/{faq}', { path: { faq: 1 } })
  const question: string = faq.data.question
  void question

  await api.patch('/v1/faqs/{faq}', { path: { faq: 1 }, body: { answer: 'New' } })
  const gone: void = await api.delete('/v1/faqs/{faq}', { path: { faq: 1 } })
  void gone

  await api.post('/v1/contact', {
    body: { name: 'A', email: 'a@example.test', message: 'Hi', captcha_id: 'x', captcha_answer: 3 },
  })

  // @ts-expect-error unknown path
  await api.get('/v1/nope')

  // @ts-expect-error path params are required
  await api.get('/v1/faqs/{faq}')

  // @ts-expect-error wrong param type
  await api.get('/v1/faqs/{faq}', { path: { faq: 'one' } })

  // @ts-expect-error body field has the wrong type
  await api.patch('/v1/faqs/{faq}', { path: { faq: 1 }, body: { sort_order: 'first' } })

  // @ts-expect-error GET-only path
  await api.delete('/v1/health')
}
