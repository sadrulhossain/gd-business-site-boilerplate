# Deploy to a VPS

Target: one Linux VPS (Debian/Ubuntu), Docker Engine with the Compose plugin, a domain you control. Size: [SIZING.md](SIZING.md).
The production stack is `docker-compose.prod.yml` with its own env file. The dev file (`docker-compose.yml`) is never used in production.

## What runs

| Service | Image | Notes |
|---------|-------|-------|
| `nginx` | built from `docker/prod/nginx.Dockerfile` | the only published ports (80, 443). TLS from mounted certs. Serves the admin (built static files) and proxies the portal and the API. Also builds the admin |
| `portal` | built from `docker/prod/portal.Dockerfile` | `next start`. Builds the site on its first start (needs the API, so it cannot build earlier), about 2 to 4 minutes |
| `php`, `php-queue`, `php-scheduler` | built from `docker/prod/php.Dockerfile` | code and vendor baked in, opcache, migrations on start |
| `mariadb` | `mariadb:<pin>` | **no published port**, data in the `mariadb-data` volume |

No Mailpit, no dev servers, no Redis (the app uses MariaDB for cache, sessions and queue). Every service has a restart policy (`always`), a healthcheck and rotated logs.

## 1. DNS

Create three records pointing at the server: `example.com`, `admin.example.com`, `api.example.com`.

## 2. Server

```sh
# Docker Engine + Compose plugin: https://docs.docker.com/engine/install/
sudo ufw allow OpenSSH && sudo ufw allow 80,443/tcp && sudo ufw enable
git clone <your fork> /opt/site && cd /opt/site && git checkout v1.0.0
```

Add 1 GB swap if the server has 1 GB of RAM. Do not publish any other port: the database must stay private.

## 3. TLS certificate

One certificate must cover all three names (a wildcard `*.example.com` plus `example.com`, or a SAN certificate). With Let's Encrypt and certbot:

```sh
sudo certbot certonly --standalone -d example.com -d admin.example.com -d api.example.com
sudo mkdir -p /etc/gdsite/certs
sudo ln -sf /etc/letsencrypt/live/example.com/fullchain.pem /etc/gdsite/certs/fullchain.pem
sudo ln -sf /etc/letsencrypt/live/example.com/privkey.pem  /etc/gdsite/certs/privkey.pem
```

Symlinks into `/etc/letsencrypt` do not resolve inside the container, so copy the real files instead:
`sudo cp -L /etc/letsencrypt/live/example.com/{fullchain,privkey}.pem /etc/gdsite/certs/`. Renewal (monthly cron, or a certbot deploy hook):
renew, copy the files again, then `docker compose -f docker-compose.prod.yml --env-file .env.prod exec nginx nginx -s reload`.
(Certbot's standalone mode needs port 80 free: run it before the first start, and use `--webroot` or a DNS challenge for renewals.)
If a load balancer or CDN terminates TLS in front, mount its origin certificate here and set `TRUSTED_PROXY_CIDR` to the balancer's address range only.

## 4. Configure

```sh
cp .env.prod.example .env.prod
chmod 600 .env.prod
```

Edit `.env.prod`: `APP_DOMAIN`, `APP_NAME`, `TLS_CERT_DIR`, the SMTP settings, `CONTACT_NOTIFY_TO`, and replace every `change-me`. Generate the secrets with
`openssl rand -base64 32` (`APP_KEY` is `base64:` plus that value). `SUPERADMIN_EMAIL` / `SUPERADMIN_PASSWORD` become the first admin. `LOCALE_SUPPORTED=en,bn` enables a second language.
Compose refuses to start when a required secret is missing. **Keep a copy of `.env.prod` outside the server** (a password manager): losing `APP_KEY` loses encrypted data.

## 5. First start

```sh
docker compose -f docker-compose.prod.yml --env-file .env.prod up -d --build --wait
docker compose -f docker-compose.prod.yml --env-file .env.prod exec php php artisan db:seed --force
```

`--wait` returns when every healthcheck passes (the portal needs its first build, so allow several minutes; follow it with `logs -f portal`). The seeders create the roles and permissions, menus,
default sections, settings, the superadmin from `.env.prod` and the admin OAuth client. They are idempotent, so running them again is safe; they never overwrite edited content.

Check: `https://example.com` (portal), `https://admin.example.com` (sign in), `https://api.example.com/api/v1/health` (`"status":"ok"`).
Then in the admin: Settings (name, address, phone, email, hours), Sections (visibility and text), and change the superadmin password.

To run the production commands shorter: `alias dcp='docker compose -f docker-compose.prod.yml --env-file .env.prod'`.

## 6. Backups

```sh
sh scripts/backup.sh      # backups/<timestamp>/{database.sql.gz,uploads.tar.gz,oauth-keys.tar.gz}, keeps 14 days (KEEP_DAYS)
```

Schedule it and copy the result off the server:

```cron
15 2 * * *  cd /opt/site && sh scripts/backup.sh >> backups/backup.log 2>&1
```

The script checks that the dump is complete and the archives are valid, and fails loudly otherwise. It dumps MariaDB with `mariadb-dump` (the stack has no PostgreSQL and no Redis).
`.env.prod` is deliberately not in the backup. **Test a restore** on a spare server before you need it:

```sh
sh scripts/restore.sh backups/20261113-021500     # asks for "yes"; replaces database, uploads and OAuth keys
```

## 7. Operate

| Task | Command |
|------|---------|
| Status | `dcp ps` (all `healthy`) |
| Logs | `dcp logs -f --tail 100 nginx portal php` |
| Restart one service | `dcp restart portal` |
| Artisan | `dcp exec php php artisan <command>` |
| Failed queue jobs | `dcp exec php php artisan queue:failed` |
| Stop | `dcp down` (volumes stay; never add `-v` unless you mean to delete the data) |

Disk: uploads and the database grow; `docker system df` and `df -h` monthly. Updating: [UPGRADE.md](UPGRADE.md).

## Hardening notes

- The production CSP drops `unsafe-eval` and, for the admin, `unsafe-inline` scripts. The portal still needs `unsafe-inline` for Next's bootstrap script (nonces are the next step, see SECURITY.md finding 5).
- HSTS is sent on every HTTPS response. Do not enable it for a domain you cannot keep on HTTPS.
- Only 22, 80 and 443 should be reachable from the internet. MariaDB is not published; verify with `ss -ltnp`.
- Rotate `PORTAL_REVALIDATE_SECRET`, the database passwords and the superadmin password if `.env.prod` was ever exposed (change the file, then `up -d` and, for the superadmin, re-run the seeder).
