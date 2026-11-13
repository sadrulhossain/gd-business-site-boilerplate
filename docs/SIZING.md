# Sizing

Measured 2026-11-13 with `docker stats` and `/proc/<pid>/status` (VmHWM) in a Docker Desktop VM with 3.7 GiB, after 300 portal page requests and 150 API list requests.

| Process (production shape) | Idle | Peak seen |
|----------------------------|------|-----------|
| Next `next start` (portal) | 119 MiB | 132 MiB |
| php-fpm (5 workers) and app | 48 MiB | 51 MiB |
| php queue worker | 28 MiB | - |
| php scheduler | 41 MiB | - |
| MariaDB 11.8 (128 MiB buffer pool, the default) | 94 MiB | 96 MiB |
| nginx | 10 MiB | 10 MiB |
| **Total containers** | **about 340 MiB** | **about 365 MiB** |

The dev stack is much larger (Next dev 340 to 580 MiB, Vite about 100 MiB); do not size from it. Mailpit (28 MiB) is dev-only.

**Minimum VPS: 1 vCPU, 1 GB RAM, 10 GB disk** to run the stack (about 365 MiB for the containers, plus the OS and page cache, leaves around 400 MiB of headroom), with 1 GB of swap as a safety net.
**Recommended: 2 vCPU, 2 GB RAM.** That also covers `next build` and `composer install` on the box; the build is memory-hungry and was not measured separately, so on a 1 GB host build in CI and ship images.
Disk grows with uploads (each image keeps the original plus up to 4 WebP variants) and the database: plan about 1 GB per 300 photos.
