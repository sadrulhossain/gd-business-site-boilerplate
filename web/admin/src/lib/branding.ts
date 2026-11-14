import { useQuery } from '@tanstack/react-query'
import { useEffect } from 'react'
import { api } from '../api'

export interface MediaInfo {
  url: string
}

export interface Branding {
  site_name: string
  logo: MediaInfo | null
  favicon: MediaInfo | null
}

/** Site name, logo and favicon from the public settings (no sign-in needed, so the sign-in screens can use it too). */
export function useBranding(): Branding | undefined {
  const query = useQuery({
    queryKey: ['branding'],
    staleTime: 5 * 60_000,
    retry: false,
    queryFn: async () => ((await api.request('GET', '/v1/public/settings')) as { data: Branding }).data,
  })

  return query.data
}

/** Points the tab icon at the uploaded favicon; the built-in icon stays when none is uploaded. */
export function useFavicon() {
  const favicon = useBranding()?.favicon?.url

  useEffect(() => {
    if (!favicon) return
    let link = document.querySelector<HTMLLinkElement>('link[rel="icon"]')
    if (!link) {
      link = document.createElement('link')
      link.rel = 'icon'
      document.head.appendChild(link)
    }
    link.removeAttribute('type')
    link.href = favicon
  }, [favicon])
}
