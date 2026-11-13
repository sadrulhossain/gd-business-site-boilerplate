# Production portal: Next.js as a Node server. The site is built when the container first starts (portal-start.sh),
# because the build prerenders pages from the API, which is not reachable while the image is being built.
# Build context: the repository root (docker-compose.prod.yml).
ARG NODE_VERSION
FROM node:${NODE_VERSION}-alpine

ENV NODE_ENV=production NEXT_TELEMETRY_DISABLED=1 COREPACK_ENABLE_DOWNLOAD_PROMPT=0
WORKDIR /app
RUN corepack enable

COPY package.json pnpm-lock.yaml pnpm-workspace.yaml ./
COPY packages ./packages
COPY web/portal/package.json ./web/portal/package.json
# web/admin is part of the workspace; its manifest is needed for a frozen install but its code is not.
COPY web/admin/package.json ./web/admin/package.json
RUN pnpm install --frozen-lockfile --filter portal... --prod=false --reporter=silent

COPY web/portal ./web/portal
# The bundled npm is never used at run time (see docs/SECURITY.md).
RUN rm -rf /usr/local/lib/node_modules/npm /usr/local/bin/npm /usr/local/bin/npx \
 && mkdir -p /app/web/portal/.next \
 && chown -R node:node /app

ARG IMAGE_ID=dev
RUN echo "$IMAGE_ID" > /app/IMAGE_ID
COPY docker/prod/portal-start.sh /usr/local/bin/portal-start.sh
RUN chmod +x /usr/local/bin/portal-start.sh

USER node
WORKDIR /app/web/portal
EXPOSE 3000
CMD ["portal-start.sh"]
