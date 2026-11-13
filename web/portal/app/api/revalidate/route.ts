import { createHmac, timingSafeEqual } from 'node:crypto'
import { revalidateTag } from 'next/cache'

const MAX_AGE_SECONDS = 300

/**
 * Receives the API's signed content-change webhook: headers `X-Webhook-Timestamp` and `X-Webhook-Signature: sha256=<hmac>`
 * where hmac = HMAC-SHA256(PORTAL_REVALIDATE_SECRET, "<timestamp>.<raw body>"), body `{"tags":["faqs", ...]}`.
 * Tags are the API table names the portal's fetches are tagged with (see lib/api.ts).
 */
export async function POST(request: Request) {
  const secret = process.env.PORTAL_REVALIDATE_SECRET
  if (!secret) return Response.json({ error: 'not configured' }, { status: 503 })

  const body = await request.text()
  const timestamp = request.headers.get('x-webhook-timestamp') ?? ''
  const signature = request.headers.get('x-webhook-signature') ?? ''

  const expected = `sha256=${createHmac('sha256', secret).update(`${timestamp}.${body}`).digest('hex')}`
  const a = Buffer.from(signature)
  const b = Buffer.from(expected)
  const fresh = Math.abs(Date.now() / 1000 - Number(timestamp)) <= MAX_AGE_SECONDS

  if (a.length !== b.length || !timingSafeEqual(a, b) || !fresh) {
    return Response.json({ error: 'invalid signature' }, { status: 401 })
  }

  let tags: unknown
  try {
    tags = (JSON.parse(body) as { tags?: unknown }).tags
  } catch {
    return Response.json({ error: 'invalid body' }, { status: 400 })
  }
  if (!Array.isArray(tags) || !tags.every((t) => typeof t === 'string')) {
    return Response.json({ error: 'invalid tags' }, { status: 400 })
  }

  // Expire immediately so the next request fetches fresh data.
  for (const tag of tags as string[]) revalidateTag(tag, { expire: 0 })

  return Response.json({ revalidated: tags })
}
