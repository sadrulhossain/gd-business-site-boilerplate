import { createBrowserRouter } from 'react-router'
import { RequireAuth, RequirePermission } from './auth/guards'
import { CrudPage } from './components/CrudPage'
import { AppShell } from './layout/AppShell'
import { modules } from './modules'
import { Callback, SignedOut } from './pages/auth'
import { Dashboard } from './pages/Dashboard'
import { NotFound } from './pages/errors'
import { Showcase } from './showcase'

export const router = createBrowserRouter([
  // Public: the OAuth redirect target, the post-logout page and the theme showcase.
  { path: '/auth/callback', element: <Callback /> },
  { path: '/signed-out', element: <SignedOut /> },
  { path: '/showcase', element: <Showcase /> },

  // Everything else needs a session; each page also needs its permission (403 otherwise).
  {
    element: (
      <RequireAuth>
        <AppShell />
      </RequireAuth>
    ),
    children: [
      {
        index: true,
        element: (
          <RequirePermission permission="dashboard.view">
            <Dashboard />
          </RequirePermission>
        ),
      },
      ...modules.map((config) => ({
        path: config.path.slice(1),
        element: (
          <RequirePermission permission={`${config.key}.view`}>
            <CrudPage config={config} />
          </RequirePermission>
        ),
      })),
      { path: '*', element: <NotFound /> },
    ],
  },
])
