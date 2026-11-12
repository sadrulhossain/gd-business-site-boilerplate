import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import './styles.css'
import { Showcase } from './showcase'

// No router yet: the single showcase route is matched by path.
const page =
  location.pathname === '/showcase' ? (
    <Showcase />
  ) : (
    <p className="p-6 text-ink">
      Hello from admin (Vite). <a className="text-primary-600 underline" href="/showcase">Theme showcase</a>
    </p>
  )

createRoot(document.getElementById('app')!).render(<StrictMode>{page}</StrictMode>)