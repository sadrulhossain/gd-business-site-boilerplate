import { describe, expect, it, vi } from 'vitest'
import { ApiError } from '@gd/api-client'
import { messages } from './messagesConfig'
import { modules } from './modules'
import { checkImage, runPool, uploadFile, type UploadDeps } from './lib/upload'

const byKey = (key: string) => modules.find((m) => m.key === key)!

describe('content module configs', () => {
  it('registers services, faqs, testimonials, team and gallery on /v1 endpoints', () => {
    for (const key of ['services', 'faqs', 'testimonials', 'team', 'gallery']) {
      const m = byKey(key)
      expect(m.endpoint).toBe(`/v1/${key}`)
      expect(m.path).toBe(`/${key}`)
    }
  })

  it('image-bearing modules turn the image panel on and show a preview column', () => {
    for (const key of ['services', 'testimonials', 'team', 'gallery']) {
      expect(byKey(key).image, key).toBe(true)
      expect(byKey(key).columns.some((c) => c.key === 'image'), key).toBe(true)
    }
    expect(byKey('faqs').image).toBeUndefined()
  })

  it('image is never a form field (it has its own endpoint)', () => {
    for (const m of modules) expect(m.fields.map((f) => f.name)).not.toContain('image')
  })

  it('bilingual text fields exist where the API has _2nd columns', () => {
    expect(byKey('faqs').fields.filter((f) => f.bilingual).map((f) => f.name)).toEqual(['question', 'answer'])
    expect(byKey('gallery').fields.filter((f) => f.bilingual).map((f) => f.name)).toEqual(['title', 'caption'])
  })
})

describe('messages config', () => {
  it('is the contact inbox: read-only fields and a read endpoint', () => {
    expect(messages).toMatchObject({ key: 'contact', path: '/messages', endpoint: '/v1/contact-messages' })
    expect(messages.fields.map((f) => f.name)).toEqual(['name', 'email', 'phone', 'subject', 'message', 'created_at'])
    expect(messages.readEndpoint(7)).toBe('/v1/contact-messages/7/read')
  })
})

describe('checkImage', () => {
  it('accepts allowed types and refuses others or oversize files', () => {
    expect(checkImage({ type: 'image/png', size: 1000 })).toBeNull()
    expect(checkImage({ type: 'image/gif', size: 1000 })).toMatch(/JPEG/)
    expect(checkImage({ type: 'image/webp', size: 6 * 1024 * 1024 })).toMatch(/Larger/)
  })
})

describe('runPool', () => {
  it('runs every item with at most `limit` in flight', async () => {
    let active = 0
    let peak = 0
    const done: number[] = []

    await runPool([1, 2, 3, 4, 5, 6, 7], 3, async (n) => {
      active++
      peak = Math.max(peak, active)
      await new Promise((r) => setTimeout(r, 5))
      active--
      done.push(n)
    })

    expect(done.sort()).toEqual([1, 2, 3, 4, 5, 6, 7])
    expect(peak).toBe(3)
  })

  it('handles an empty list', async () => {
    await expect(runPool([], 3, async () => undefined)).resolves.toBeUndefined()
  })
})

/** A scripted XMLHttpRequest: each `send` plays the next response. */
function fakeDeps(responses: { status: number; body?: unknown }[], refresh = async () => true) {
  const sent: { url: string; auth?: string }[] = []
  const progress: number[] = []
  let call = 0
  const createXhr = () => {
    const headers: Record<string, string> = {}
    const xhr = {
      status: 0,
      statusText: '',
      responseText: '',
      upload: {} as { onprogress?: (e: unknown) => void },
      onload: undefined as undefined | (() => void),
      onerror: undefined as undefined | (() => void),
      open(_m: string, url: string) {
        sent.push({ url })
      },
      setRequestHeader(k: string, v: string) {
        headers[k] = v
      },
      send() {
        sent[sent.length - 1]!.auth = headers.Authorization
        const r = responses[call++]!
        xhr.upload.onprogress?.({ lengthComputable: true, loaded: 5, total: 10 })
        xhr.status = r.status
        xhr.responseText = r.body === undefined ? '' : JSON.stringify(r.body)
        xhr.onload?.()
      },
    }

    return xhr as unknown as XMLHttpRequest
  }
  const deps: UploadDeps = { createXhr, token: async () => 'tok', refresh, baseUrl: () => 'http://x/api/' }

  return { deps, sent, progress }
}

describe('uploadFile', () => {
  const file = new File(['x'], 'a.png', { type: 'image/png' })

  it('posts to the API with the bearer token, reports progress and resolves the body', async () => {
    const { deps, sent } = fakeDeps([{ status: 200, body: { data: { id: 1 } } }])
    const seen: number[] = []

    await expect(uploadFile('/v1/gallery/1/image', file, (f) => seen.push(f), { deps })).resolves.toEqual({ data: { id: 1 } })
    expect(sent[0]).toEqual({ url: 'http://x/api/v1/gallery/1/image', auth: 'Bearer tok' })
    expect(seen).toEqual([0.5, 1])
  })

  it('throws ApiError with field details on 422', async () => {
    const { deps } = fakeDeps([{ status: 422, body: { error: { code: 'validation_failed', message: 'Invalid', details: { image: ['Too big'] } } } }])
    const error = await uploadFile('/p', file, () => undefined, { deps }).catch((e: unknown) => e)

    expect(error).toBeInstanceOf(ApiError)
    expect(error).toMatchObject({ status: 422, details: { image: ['Too big'] } })
  })

  it('refreshes once on 401 and retries', async () => {
    const refresh = vi.fn(async () => true)
    const { deps, sent } = fakeDeps([{ status: 401 }, { status: 200, body: { ok: true } }], refresh)

    await expect(uploadFile('/p', file, () => undefined, { deps })).resolves.toEqual({ ok: true })
    expect(refresh).toHaveBeenCalledOnce()
    expect(sent).toHaveLength(2)
  })

  it('gives up with a 401 when the refresh fails', async () => {
    const { deps } = fakeDeps([{ status: 401 }], async () => false)

    await expect(uploadFile('/p', file, () => undefined, { deps })).rejects.toMatchObject({ status: 401 })
  })
})
