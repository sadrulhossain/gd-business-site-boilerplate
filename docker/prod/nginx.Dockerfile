# Production nginx: the admin SPA is built here (Vite, static files) and served by nginx next to the proxy config.
# Build context: the repository root (docker-compose.prod.yml).
ARG NODE_VERSION
ARG NGINX_VERSION
FROM node:${NODE_VERSION}-alpine AS admin
ARG OAUTH_ADMIN_CLIENT_ID
ENV OAUTH_ADMIN_CLIENT_ID=${OAUTH_ADMIN_CLIENT_ID} COREPACK_ENABLE_DOWNLOAD_PROMPT=0
WORKDIR /app
RUN corepack enable
COPY package.json pnpm-lock.yaml pnpm-workspace.yaml ./
COPY packages ./packages
COPY web/admin ./web/admin
COPY web/portal/package.json ./web/portal/package.json
RUN pnpm install --frozen-lockfile --filter admin... --reporter=silent
RUN cd web/admin && pnpm exec vite build

FROM nginx:${NGINX_VERSION}-alpine
COPY --from=admin /app/web/admin/dist /usr/share/nginx/admin
COPY docker/nginx/snippets /etc/nginx/snippets
COPY docker/prod/nginx/templates /etc/nginx/templates
