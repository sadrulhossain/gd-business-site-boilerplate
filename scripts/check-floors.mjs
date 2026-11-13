// Fails when an installed or pinned version is below the floor in docs/VERSIONS.md.
// Sources: .env *_VERSION pins (images), api/composer.lock (PHP packages), pnpm-lock.yaml (JS packages).
// The floors themselves are re-verified against advisories by hand each phase (see docs/SECURITY.md).
import { readFileSync, existsSync } from 'node:fs'

const rows = readFileSync('docs/VERSIONS.md', 'utf8')
  .split('\n')
  .filter((l) => l.startsWith('| ') && !l.startsWith('| Component') && !l.startsWith('|--'))
  .map((l) => l.split('|').map((c) => c.trim()))
  .map((c) => ({ name: c[1], floor: (c[3].match(/\d+\.\d+\.\d+/) ?? [])[0] }))
  .filter((r) => r.floor)

const floorOf = (re) => rows.find((r) => re.test(r.name))?.floor
const cmp = (a, b) => {
  const [x, y] = [a, b].map((v) => v.replace(/^v/, '').split('.').map(Number))
  for (let i = 0; i < 3; i++) if ((x[i] ?? 0) !== (y[i] ?? 0)) return (x[i] ?? 0) - (y[i] ?? 0)
  return 0
}

const checks = []
const env = existsSync('.env') ? readFileSync('.env', 'utf8') : readFileSync('.env.example', 'utf8')
for (const [key, row] of Object.entries({ PHP_VERSION: /^PHP$/, NODE_VERSION: /^Node/, NGINX_VERSION: /^nginx/, MARIADB_VERSION: /^MariaDB/, MAILPIT_VERSION: /^Mailpit/, COMPOSER_VERSION: /^Composer/ })) {
  const v = env.match(new RegExp(`^${key}=(.+)$`, 'm'))?.[1]
  if (v) checks.push([key, v, floorOf(row)])
}

const lock = JSON.parse(readFileSync('api/composer.lock', 'utf8'))
const php = { 'laravel/framework': /^Laravel/, 'laravel/passport': /passport/, 'spatie/laravel-permission': /spatie/, 'ezyang/htmlpurifier': /htmlpurifier/, 'phpseclib/phpseclib': /phpseclib/, 'dedoc/scramble': /scramble/ }
for (const p of [...lock.packages, ...lock['packages-dev']]) if (php[p.name]) checks.push([p.name, p.version, floorOf(php[p.name])])

const pnpm = readFileSync('pnpm-lock.yaml', 'utf8')
const js = { next: /^Next/, vite: /^Vite/, react: /^React/, 'react-dom': /^React/, tailwindcss: /^tailwindcss$/, postcss: /^postcss$/, typescript: /^typescript$/, '@tiptap/react': /tiptap\/react/, '@tiptap/starter-kit': /starter-kit/ }
const installed = new Map()
for (const line of pnpm.split(/\r?\n/)) {
  const m = /^ {2}'?(.+?)@(\d+\.\d+\.\d+)'?:$/.exec(line)
  if (m && !installed.has(m[1])) installed.set(m[1], m[2])
}
for (const [name, row] of Object.entries(js)) if (installed.has(name)) checks.push([name, installed.get(name), floorOf(row)])

let bad = 0
for (const [name, have, floor] of checks) {
  if (!floor) continue
  const ok = cmp(have, floor) >= 0
  if (!ok) bad++
  console.log(`${ok ? 'ok   ' : 'BELOW'} ${name} ${have} (floor ${floor})`)
}
console.log(bad ? `floors: ${bad} below floor` : `floors: ok (${checks.length} checked)`)
process.exit(bad ? 1 : 0)
