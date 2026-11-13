import { cleanup, render, screen, waitFor } from '@testing-library/react'
import { afterEach, describe, expect, it } from 'vitest'
import { RichText } from './components/RichText'
import { blank, payload } from './lib/crud'
import { checkPasswordChange } from './lib/profile'
import { modules } from './modules'

afterEach(cleanup)
const posts = modules.find((m) => m.key === 'posts')!
const categories = modules.find((m) => m.key === 'categories')!

describe('posts config', () => {
  it('is a CrudPage module on /v1/posts with a cover image', () => {
    expect(posts).toMatchObject({ path: '/posts', endpoint: '/v1/posts', image: true })
    expect(posts.fields.map((f) => f.name)).not.toContain('image')
  })

  it('has a bilingual rich-text body and bilingual SEO fields', () => {
    const body = posts.fields.find((f) => f.name === 'body')!
    expect(body).toMatchObject({ type: 'richtext', bilingual: true })
    expect(posts.fields.filter((f) => f.name.startsWith('seo_')).every((f) => f.bilingual)).toBe(true)
    expect(posts.fields.filter((f) => f.name.startsWith('seo_')).map((f) => f.name)).toEqual(['seo_title', 'seo_description'])
  })

  it('status is a draft/published select that starts as draft', () => {
    const status = posts.fields.find((f) => f.name === 'status')!
    expect(status.type).toBe('select')
    expect(status.options?.map((o) => o.value)).toEqual(['draft', 'published'])
    expect(blank(posts.fields)).toMatchObject({ status: 'draft', body: '', body_2nd: '' })
  })

  it('category options come from /v1/categories', () => {
    const field = posts.fields.find((f) => f.name === 'category_id')!
    expect(field.optionsFrom?.endpoint).toBe(categories.endpoint)
    expect(field.optionsFrom?.map({ id: 3, name: 'News' } as never)).toEqual({ value: 3, label: 'News' })
  })

  it('payload sends blanks as null and keeps both languages', () => {
    const body = payload(posts.fields, { title: 'T', title_2nd: '', slug: 's', status: 'published', category_id: '', published_at: '', body: '<p>x</p>', body_2nd: '' })
    expect(body).toMatchObject({ title: 'T', title_2nd: null, status: 'published', category_id: null, published_at: null, body: '<p>x</p>', body_2nd: null })
  })
})

describe('checkPasswordChange', () => {
  const ok = { current_password: 'old-password-1', password: 'a-new-long-password', password_confirmation: 'a-new-long-password' }

  it('accepts a valid change', () => {
    expect(checkPasswordChange(ok)).toEqual({})
  })

  it('reports each problem on its field', () => {
    expect(checkPasswordChange({ ...ok, current_password: '' })).toHaveProperty('current_password')
    expect(checkPasswordChange({ ...ok, password: 'short', password_confirmation: 'short' })).toHaveProperty('password')
    expect(checkPasswordChange({ ...ok, password_confirmation: 'nope' })).toHaveProperty('password_confirmation')
    expect(checkPasswordChange({ ...ok, current_password: ok.password })).toHaveProperty('password')
  })
})

describe('RichText', () => {
  it('renders the HTML value in an accessible editor with a toolbar', async () => {
    render(<RichText id="b" label="Body" value="<h2>Title</h2><p>Hello <strong>world</strong></p>" onChange={() => undefined} />)

    await waitFor(() => expect(screen.getByRole('textbox', { name: 'Body' }).textContent).toContain('Hello world'))
    expect(screen.getByRole('textbox', { name: 'Body' }).querySelector('h2')?.textContent).toBe('Title')
    expect(screen.getByRole('toolbar', { name: 'Body formatting' })).toBeTruthy()
    expect(screen.getByRole('button', { name: 'Bold' })).toBeTruthy()
  })
})
