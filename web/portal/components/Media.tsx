import type { Media } from '../lib/api'

/** An upload with its generated WebP widths as srcset. Alt is empty unless given: these are decorative by default. */
export function Picture({ image, alt = '', className, sizes = '(min-width: 768px) 33vw, 100vw' }: { image: Media; alt?: string; className?: string; sizes?: string }) {
  const srcSet = Object.entries(image.webp ?? {})
    .map(([width, url]) => `${url} ${width}w`)
    .join(', ')

  // eslint-disable-next-line @next/next/no-img-element -- images.unoptimized: the API already serves resized WebP.
  return <img src={image.url} srcSet={srcSet || undefined} sizes={srcSet ? sizes : undefined} alt={alt} loading="lazy" className={className} />
}
