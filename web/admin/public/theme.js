// Applies the saved (or system) colour theme before first paint. A separate file, not an inline script,
// because the admin's CSP allows scripts from 'self' only. Loaded from index.html; ThemeToggle changes it later.
;(function () {
  try {
    var saved = localStorage.getItem('admin-theme')
    var dark = saved ? saved === 'dark' : window.matchMedia('(prefers-color-scheme: dark)').matches
    document.documentElement.classList.toggle('dark', dark)
  } catch (e) {
    /* storage blocked: stay light */
  }
})()
