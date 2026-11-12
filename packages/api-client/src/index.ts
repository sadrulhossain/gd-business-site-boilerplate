import type { paths } from '@gd/api-types'

/**
 * Typed fetch client. Paths, path/query params, request bodies and responses all come from
 * the generated OpenAPI types, so a wrong path or payload is a compile error.
 *
 *   const api = createClient({ baseUrl: 'http://api.example.test/api', getToken: () => token })
 *   const { data } = await api.get('/v1/faqs/{faq}', { path: { faq: 1 } })
 *
 * Scramble documents resource updates as PUT; the API also accepts PATCH for partial updates,
 * so `patch` reuses the `put` types.
 */

type Method = 'get' | 'post' | 'put' | 'delete'

type PathsFor<M extends Method> = {
  [P in keyof paths]: paths[P] extends Record<M, object> ? P : never
}[keyof paths]

type Op<P extends keyof paths, M extends Method> = paths[P] extends Record<M, infer O> ? O : never

type PathParams<O> = O extends { parameters: { path: infer T } } ? T : never
type QueryParams<O> = O extends { parameters: { query?: infer Q } } ? Exclude<Q, undefined> : never
type JsonBody<O> = O extends { requestBody?: { content: { 'application/json': infer B } } } ? B : never
type FormBody<O> = O extends { requestBody?: { content: { 'multipart/form-data': unknown } } } ? FormData : never
type RequiredBody<O> = O extends { requestBody: object } ? true : false

type JsonResponse<R, Status extends number> = R extends { [S in Status]: { content: { 'application/json': infer J } } }
  ? J
  : never
type Success<O> = O extends { responses: infer R } ? JsonResponse<R, 200> | JsonResponse<R, 201> : never

/** Resolved response body; `void` for 204 and other bodiless successes. */
export type ResponseOf<O> = [Success<O>] extends [never] ? void : Success<O>

type Init<O> = ([PathParams<O>] extends [never] ? { path?: never } : { path: PathParams<O> }) &
  ([QueryParams<O>] extends [never] ? { query?: never } : { query?: QueryParams<O> }) &
  ([JsonBody<O> | FormBody<O>] extends [never] ? { body?: never } : { body?: JsonBody<O> | FormBody<O> })

type NeedsInit<O> = [PathParams<O>] extends [never] ? RequiredBody<O> : true
type Args<O> = NeedsInit<O> extends true ? [init: Init<O>] : [init?: Init<O>]

export interface ClientOptions {
  /** Everything before the path, e.g. `http://api.example.test/api` (the spec paths start at `/v1`). */
  baseUrl: string
  /** Bearer token for authenticated routes; called per request. */
  getToken?: () => string | null | undefined | Promise<string | null | undefined>
  /** Default `?lang=` for public reads (`/v1/public/*`). */
  lang?: string
  fetch?: typeof fetch
}

/** Non-2xx answer. `code`, `message` and `details` come from the API's `{"error": {...}}` body. */
export class ApiError extends Error {
  constructor(
    readonly status: number,
    readonly code: string,
    message: string,
    /** Field => messages for 422 validation errors. */
    readonly details?: Record<string, string[]>,
  ) {
    super(message)
    this.name = 'ApiError'
  }
}

export function createClient(options: ClientOptions) {
  const doFetch = options.fetch ?? ((...args: Parameters<typeof fetch>) => fetch(...args))

  async function request(
    method: 'GET' | 'POST' | 'PUT' | 'PATCH' | 'DELETE',
    path: string,
    // Loosely typed on purpose: the public methods below carry the precise per-operation types.
    init: { path?: unknown; query?: unknown; body?: unknown } = {},
  ): Promise<unknown> {
    const pathParams = (init.path ?? {}) as Record<string, unknown>
    const query = { ...(init.query as Record<string, unknown> | undefined) }
    if (options.lang && path.startsWith('/v1/public/') && query.lang === undefined) {
      query.lang = options.lang
    }

    const filled = path.replace(/\{(\w+)\}/g, (_, name: string) => encodeURIComponent(String(pathParams[name])))
    const search = new URLSearchParams()
    for (const [key, value] of Object.entries(query)) {
      if (value !== undefined && value !== null) search.set(key, String(value))
    }
    const url = options.baseUrl.replace(/\/+$/, '') + filled + (search.size ? `?${search}` : '')

    const headers: Record<string, string> = { Accept: 'application/json' }
    const token = await options.getToken?.()
    if (token) headers.Authorization = `Bearer ${token}`

    let body: BodyInit | undefined
    if (init.body instanceof FormData) {
      body = init.body // the browser sets the multipart boundary
    } else if (init.body !== undefined) {
      body = JSON.stringify(init.body)
      headers['Content-Type'] = 'application/json'
    }

    const response = await doFetch(url, { method, headers, body })

    if (response.status === 204) return undefined
    const payload: unknown = await response.json().catch(() => undefined)

    if (!response.ok) {
      const error = (payload as { error?: { code?: string; message?: string; details?: Record<string, string[]> } } | undefined)?.error
      throw new ApiError(response.status, error?.code ?? 'http_error', error?.message ?? response.statusText, error?.details)
    }

    return payload
  }

  return {
    get: <P extends PathsFor<'get'>>(path: P, ...args: Args<Op<P, 'get'>>) =>
      request('GET', path, args[0]) as Promise<ResponseOf<Op<P, 'get'>>>,
    post: <P extends PathsFor<'post'>>(path: P, ...args: Args<Op<P, 'post'>>) =>
      request('POST', path, args[0]) as Promise<ResponseOf<Op<P, 'post'>>>,
    put: <P extends PathsFor<'put'>>(path: P, ...args: Args<Op<P, 'put'>>) =>
      request('PUT', path, args[0]) as Promise<ResponseOf<Op<P, 'put'>>>,
    patch: <P extends PathsFor<'put'>>(path: P, ...args: Args<Op<P, 'put'>>) =>
      request('PATCH', path, args[0]) as Promise<ResponseOf<Op<P, 'put'>>>,
    delete: <P extends PathsFor<'delete'>>(path: P, ...args: Args<Op<P, 'delete'>>) =>
      request('DELETE', path, args[0]) as Promise<ResponseOf<Op<P, 'delete'>>>,
  }
}

export type ApiClient = ReturnType<typeof createClient>
export type { components, paths } from '@gd/api-types'
