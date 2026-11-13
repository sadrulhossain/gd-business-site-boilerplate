import { ApiError } from '@gd/api-client'
import { getValidToken, refreshTokens } from '../auth/oauth'
import { config } from './config'

export const IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/webp']
/** Mirrors UPLOADS_MAX_MB for a quick message; the API stays the authority. */
export const MAX_IMAGE_MB = 5

/** A message when the browser can already tell the file will be refused, else null. */
export function checkImage(file: { type: string; size: number }): string | null {
  if (!IMAGE_TYPES.includes(file.type)) return 'Only JPEG, PNG or WebP images'
  if (file.size > MAX_IMAGE_MB * 1024 * 1024) return `Larger than ${MAX_IMAGE_MB} MB`

  return null
}

export interface UploadDeps {
  createXhr: () => XMLHttpRequest
  token: () => Promise<string | null>
  refresh: () => Promise<boolean>
  baseUrl: () => string
}

const defaults: UploadDeps = { createXhr: () => new XMLHttpRequest(), token: getValidToken, refresh: refreshTokens, baseUrl: config.apiBase }

/**
 * POST a file as multipart field `field` with upload progress (fetch cannot report it). Resolves with the parsed JSON body.
 * A 401 refreshes the token once and retries; other failures throw ApiError like the API client does.
 */
export function uploadFile(
  path: string,
  file: File,
  onProgress: (fraction: number) => void,
  { field = 'image', deps = defaults, retried = false }: { field?: string; deps?: UploadDeps; retried?: boolean } = {},
): Promise<unknown> {
  return new Promise((resolve, reject) => {
    void deps.token().then((token) => {
      const xhr = deps.createXhr()
      xhr.open('POST', deps.baseUrl().replace(/\/+$/, '') + path)
      xhr.setRequestHeader('Accept', 'application/json')
      if (token) xhr.setRequestHeader('Authorization', `Bearer ${token}`)

      xhr.upload.onprogress = (e) => {
        if (e.lengthComputable && e.total > 0) onProgress(e.loaded / e.total)
      }
      xhr.onerror = () => reject(new ApiError(0, 'network_error', 'Network error'))
      xhr.onload = () => {
        if (xhr.status === 401 && !retried) {
          void deps.refresh().then((renewed) => {
            if (!renewed) return reject(new ApiError(401, 'unauthenticated', 'Unauthenticated'))
            uploadFile(path, file, onProgress, { field, deps, retried: true }).then(resolve, reject)
          })
          return
        }

        let body: unknown
        try {
          body = JSON.parse(xhr.responseText)
        } catch {
          body = undefined
        }

        if (xhr.status >= 200 && xhr.status < 300) {
          onProgress(1)
          return resolve(body)
        }

        const error = (body as { error?: { code?: string; message?: string; details?: Record<string, string[]> } } | undefined)?.error
        reject(new ApiError(xhr.status, error?.code ?? 'http_error', error?.message ?? xhr.statusText, error?.details))
      }

      const form = new FormData()
      form.append(field, file)
      xhr.send(form)
    }, reject)
  })
}

/** Runs `worker` over `items` with at most `limit` in flight. Never rejects: worker errors are the worker's to record. */
export async function runPool<T>(items: T[], limit: number, worker: (item: T) => Promise<void>): Promise<void> {
  let next = 0
  const lane = async () => {
    while (next < items.length) await worker(items[next++]!)
  }

  await Promise.all(Array.from({ length: Math.min(limit, items.length) }, lane))
}
