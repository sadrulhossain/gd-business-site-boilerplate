/** Full-page navigation (the OAuth redirects). A seam so tests can observe it instead of leaving the page. */
export function navigate(url: string) {
  window.location.assign(url)
}
