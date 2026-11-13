const apiBase = () => (process.env.API_INTERNAL_URL ?? 'http://api.localhost/api').replace(/\/+$/, '')

/** A fresh captcha challenge `{data: {id, question}}`. Never cached: each one works once. */
export async function GET(request: Request) {
  const forwardedFor = request.headers.get('x-forwarded-for')

  try {
    const response = await fetch(`${apiBase()}/v1/public/captcha`, {
      headers: { Accept: 'application/json', ...(forwardedFor ? { 'X-Forwarded-For': forwardedFor } : {}) },
      cache: 'no-store',
    })

    return new Response(await response.text(), { status: response.status, headers: { 'Content-Type': 'application/json', 'Cache-Control': 'no-store' } })
  } catch {
    return Response.json({ error: { code: 'unavailable', message: 'The service is unavailable.' } }, { status: 502 })
  }
}
