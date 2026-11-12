/**
 * Where things live. Functions, not constants, so they follow the current origin (and tests can change it).
 * The admin origin proxies /api and /oauth/token to the API (nginx), so the SPA is same-origin with them;
 * only the browser login page (/oauth/authorize, /login, /logout) is on the api.* origin.
 */
export const config = {
  clientId: __OAUTH_CLIENT_ID__,
  redirectUri: () => `${location.origin}/auth/callback`,
  apiBase: () => `${location.origin}/api`,
  tokenUrl: () => `${location.origin}/oauth/token`,
  /** admin.example.test:28080 -> api.example.test:28080 */
  apiOrigin: () => location.origin.replace('//admin.', '//api.'),
}
