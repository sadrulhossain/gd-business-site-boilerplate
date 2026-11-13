import { notFound } from 'next/navigation'

/** Any path no page matches ends here, so the localised not-found (inside the locale layout) answers it with a 404. */
export default function CatchAll(): never {
  notFound()
}
