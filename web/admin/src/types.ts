// Response shapes of endpoints whose OpenAPI schema is only loosely typed (inline JSON responses).

export interface Me {
  id: number
  name: string
  email: string
  is_superadmin: boolean
  roles: string[]
  permissions: string[]
}

export interface MenuItem {
  id: number
  title: string
  icon: string | null
  path: string | null
  permission_name: string | null
  children: MenuItem[]
}

export interface Locales {
  default: string
  fallback: string
  supported: string[]
}

/** Laravel resource collection; `meta` is present only on paginated lists. */
export interface Listing<Row> {
  data: Row[]
  meta?: { current_page: number; last_page: number; total: number }
}
