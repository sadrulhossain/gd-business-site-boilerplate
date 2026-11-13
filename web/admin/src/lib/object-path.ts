/** Read `a.b.c` from nested objects; undefined when any step is missing. */
export function getPath(source: unknown, path: string): unknown {
  return path.split('.').reduce<unknown>((value, key) => (value !== null && typeof value === 'object' ? (value as Record<string, unknown>)[key] : undefined), source)
}

/** A copy of `source` with `a.b.c` set to `value`; objects along the path are copied, never mutated. */
export function setPath<T extends Record<string, unknown>>(source: T, path: string, value: unknown): T {
  const [head, ...rest] = path.split('.')
  if (head === undefined) return source
  if (rest.length === 0) return { ...source, [head]: value }

  const child = source[head]

  return { ...source, [head]: setPath(child !== null && typeof child === 'object' ? (child as Record<string, unknown>) : {}, rest.join('.'), value) }
}

/** Deep copy where every `''` becomes null, the shape the API takes for "no value". */
export function blanksToNull(value: unknown): unknown {
  if (value === '') return null
  if (Array.isArray(value)) return value.map(blanksToNull)
  if (value !== null && typeof value === 'object') {
    return Object.fromEntries(Object.entries(value).map(([key, inner]) => [key, blanksToNull(inner)]))
  }

  return value
}
