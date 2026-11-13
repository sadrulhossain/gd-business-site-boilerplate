import type { ComponentType } from 'react'
import { createBrowserRouter } from 'react-router'
import { RequireAuth, RequirePermission } from './auth/guards'
import { CrudPage, type CrudConfig } from './components/CrudPage'
import { GalleryUploader } from './components/GalleryUploader'
import { AppShell } from './layout/AppShell'
import { messages } from './messagesConfig'
import { modules } from './modules'
import { Callback, SignedOut } from './pages/auth'
import { Dashboard } from './pages/Dashboard'
import { NotFound } from './pages/errors'
import { MessagesPage } from './pages/Messages'
import { SettingsPage } from './pages/Settings'
import { settings } from './settingsConfig'
import { Showcase } from './showcase'

/** Plain CrudPage plus the gallery's bulk uploader above it. */
function GalleryPage({ config }: { config: CrudConfig }) {
  return (
    <div className="space-y-4">
      <GalleryUploader endpoint={config.endpoint} />
      <CrudPage config={config} />
    </div>
  )
}

/** Modules that need more than the plain page; everything else renders CrudPage directly. */
const pages: Record<string, ComponentType<{ config: CrudConfig }>> = { gallery: GalleryPage }

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
      ...modules.map((config) => {
        const Page = pages[config.key] ?? CrudPage

        return {
          path: config.path.slice(1),
          element: (
            <RequirePermission permission={`${config.key}.view`}>
              <Page config={config} />
            </RequirePermission>
          ),
        }
      }),
      {
        path: settings.path.slice(1),
        element: (
          <RequirePermission permission={`${settings.key}.view`}>
            <SettingsPage config={settings} />
          </RequirePermission>
        ),
      },
      {
        path: messages.path.slice(1),
        element: (
          <RequirePermission permission={`${messages.key}.view`}>
            <MessagesPage config={messages} />
          </RequirePermission>
        ),
      },
      { path: '*', element: <NotFound /> },
    ],
  },
])
