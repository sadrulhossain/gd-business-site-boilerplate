const apiBase = () => (process.env.API_INTERNAL_URL ?? 'http://api.localhost/api').replace(/\/+$/, '')

/**
 * Browser form -> API. The portal forwards the JSON as is (captcha id/answer and the honeypot included) and relays the
 * API's status and body, so 422 field errors and 429 throttling reach the form. The browser never needs the API origin.
 */
export async function POST(request: Request) {
  const body = await request.text()
  const forwardedFor = request.headers.get('x-forwarded-for')

  try {
    const response = await fetch(`${apiBase()}/v1/contact`, {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        // The API's per-IP throttle should see the visitor, not this server.
        ...(forwardedFor ? { 'X-Forwarded-For': forwardedFor } : {}),
      },
      body,
      cache: 'no-store',
    })

    return new Response(await response.text(), { status: response.status, headers: { 'Content-Type': 'application/json', 'Cache-Control': 'no-store' } })
  } catch {
    return Response.json({ error: { code: 'unavailable', message: 'The service is unavailable.' } }, { status: 502 })
  }
}
