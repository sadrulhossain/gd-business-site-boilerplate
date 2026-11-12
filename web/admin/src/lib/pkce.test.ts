import { describe, expect, it } from 'vitest'
import { challengeFor, randomString } from './pkce'

describe('pkce', () => {
  it('matches the RFC 7636 S256 example', async () => {
    expect(await challengeFor('dBjftJeZ4CVP-mB92K27uhbUJU1p1r_wW1gFWFOEjXk')).toBe(
      'E9Melhoa2OwvFrEMTJguCHaoeK1t8URWbuGJSstw-cM',
    )
  })

  it('makes url-safe random strings that differ each time', () => {
    const a = randomString(48)
    expect(a).toMatch(/^[A-Za-z0-9_-]+$/)
    expect(randomString(48)).not.toBe(a)
  })
})
