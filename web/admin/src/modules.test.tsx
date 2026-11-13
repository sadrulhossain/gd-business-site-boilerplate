import { describe, expect, it } from 'vitest'
import { blank, payload } from './lib/crud'
import { blanksToNull, getPath, setPath } from './lib/object-path'
import { moveId } from './lib/reorder'
import { modules, permissionOption } from './modules'
import { settingsKeys, settings } from './settingsConfig'
import { settingsPayload } from './pages/Settings'

const byKey = (key: string) => modules.find((m) => m.key === key)!

describe('module configs', () => {
  it('registers every admin module once, with unique keys and paths', () => {
    for (const key of ['users', 'roles', 'menus', 'sections']) expect(byKey(key)).toBeDefined()
    expect(new Set(modules.map((m) => m.key)).size).toBe(modules.length)
    expect(new Set(modules.map((m) => m.path)).size).toBe(modules.length)
    expect(modules.map((m) => m.path)).not.toContain(settings.path)
  })

  it('is well formed: v1 endpoints, matching paths, unique field names, a column per header', () => {
    for (const m of modules) {
      expect(m.endpoint).toMatch(/^\/v1\//)
      expect(m.path).toBe(`/${m.key}`)
      expect(m.columns.length).toBeGreaterThan(0)
      const names = m.fields.map((f) => f.name)
      expect(new Set(names).size, m.key).toBe(names.length)
      for (const f of m.fields) if (f.bilingual) expect(['text', 'textarea', 'richtext', undefined]).toContain(f.type)
    }
  })

  it('points reorder at <endpoint>/reorder', () => {
    for (const key of ['sections', 'menus']) expect(byKey(key).reorder?.endpoint).toBe(`${byKey(key).endpoint}/reorder`)
  })

  it('sections: edit-only, toggle column, bilingual title and content', () => {
    const s = byKey('sections')
    expect([s.canCreate, s.canDelete]).toEqual([false, false])
    expect(s.fields.filter((f) => f.bilingual).map((f) => f.name)).toEqual(['title', 'content'])
  })

  it('menus: edit-only, reorder stays within one parent', () => {
    const m = byKey('menus')
    expect([m.canCreate, m.canDelete]).toEqual([false, false])
    const group = m.reorder!.group!
    expect(group({ id: 1, parent_id: 5 })).toBe(group({ id: 2, parent_id: 5 }))
    expect(group({ id: 3, parent_id: null })).not.toBe(group({ id: 2, parent_id: 5 }))
  })

  it('users: roles come from the API as a checkgroup, password is a password field', () => {
    const u = byKey('users')
    const roles = u.fields.find((f) => f.name === 'roles')!
    expect(roles.type).toBe('checkgroup')
    expect(roles.optionsFrom?.endpoint).toBe('/v1/roles')
    expect(u.fields.find((f) => f.name === 'password')?.type).toBe('password')
    expect(blank(u.fields)).toMatchObject({ roles: [], password: '' })
  })

  it('roles: permissions matrix is built from permission names', () => {
    const r = byKey('roles').fields.find((f) => f.name === 'permissions')!
    expect(r.optionsFrom?.endpoint).toBe('/v1/permissions')
    expect(permissionOption('faqs.view')).toEqual({ value: 'faqs.view', label: 'view', group: 'faqs' })
    expect(permissionOption('dashboard.view').group).toBe('dashboard')
  })

  it('payload sends only configured fields, with blanks as null', () => {
    const u = byKey('users')
    expect(payload(u.fields, { name: 'A', email: '', password: '', roles: ['x'], id: 9, extra: 1 })).toEqual({ name: 'A', email: null, password: null, roles: ['x'] })
  })
})

describe('settings config', () => {
  it('has tabs with unique ids and unique field names', () => {
    expect(new Set(settings.tabs.map((t) => t.id)).size).toBe(settings.tabs.length)
    const names = settings.tabs.flatMap((t) => t.fields.map((f) => f.name))
    expect(new Set(names).size).toBe(names.length)
  })

  it('covers every API key and sends only those', () => {
    expect(settingsKeys(settings).sort()).toEqual(
      ['address', 'address_2nd', 'email', 'hours', 'phone', 'site_name', 'site_name_2nd', 'social_links', 'tagline', 'tagline_2nd'].sort(),
    )
    const body = settingsPayload(settings, { site_name: 'S', phone: '', updated_at: 'x', hours: { mon: { open: '', close: '', closed: true } }, social_links: { x: '' } })
    expect(body).toMatchObject({ site_name: 'S', phone: null, hours: { mon: { open: null, close: null, closed: true } }, social_links: { x: null } })
    expect(body).not.toHaveProperty('updated_at')
  })

  it('has bilingual site name and a field per weekday and network', () => {
    expect(settings.tabs[0]!.fields[0]).toMatchObject({ name: 'site_name', bilingual: true })
    const names = settings.tabs.flatMap((t) => t.fields.map((f) => f.name))
    expect(names.filter((n) => n.startsWith('hours.'))).toHaveLength(21)
    expect(names).toContain('social_links.whatsapp')
  })
})

describe('helpers', () => {
  it('moveId puts the dragged id at the target position', () => {
    expect(moveId([1, 2, 3, 4], 1, 3)).toEqual([2, 3, 1, 4])
    expect(moveId([1, 2, 3, 4], 4, 1)).toEqual([4, 1, 2, 3])
    const same = [1, 2]
    expect(moveId(same, 1, 1)).toBe(same)
    expect(moveId(same, 1, 9)).toBe(same)
  })

  it('getPath / setPath work on nested objects without mutating', () => {
    const src = { hours: { mon: { open: '09:00' } } }
    expect(getPath(src, 'hours.mon.open')).toBe('09:00')
    expect(getPath(src, 'hours.tue.open')).toBeUndefined()
    const next = setPath(src, 'hours.tue.open', '10:00')
    expect(getPath(next, 'hours.tue.open')).toBe('10:00')
    expect(src).toEqual({ hours: { mon: { open: '09:00' } } })
  })

  it('blanksToNull converts empty strings deeply', () => {
    expect(blanksToNull({ a: '', b: [''], c: { d: '', e: 0, f: false } })).toEqual({ a: null, b: [null], c: { d: null, e: 0, f: false } })
  })
})
