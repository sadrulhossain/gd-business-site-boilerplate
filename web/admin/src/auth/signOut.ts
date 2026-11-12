import { api } from '../api'
import { config } from '../lib/config'
import { navigate } from '../lib/navigate'
import { useAuth } from './store'

/** Revokes the token, drops it from memory, and ends the API's browser session (which returns to /signed-out). */
export async function signOut() {
  try {
    await api.request('POST', '/v1/logout')
  } catch {
    // Already expired or unreachable: leaving is still right.
  }
  useAuth.getState().clear()
  navigate(`${config.apiOrigin()}/logout`)
}
