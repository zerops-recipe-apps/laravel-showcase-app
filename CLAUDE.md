# laravel-showcase-app

Laravel 13 recipe on Zerops with PostgreSQL, Valkey (Redis), S3-compatible object storage, Meilisearch, and a background queue worker — three setups: `prod` (HTTP), `dev` (HTTP + SSH live edit), `worker` (no HTTP).

## Zerops service facts

- HTTP port: `80` (document root `public/`)
- Siblings: `db` (PostgreSQL), `redis` (Valkey), `storage` (S3/MinIO), `search` (Meilisearch) — env vars follow the `${hostname_*}` pattern (`DB_*`, `REDIS_*`, `AWS_*`, `MEILISEARCH_HOST/KEY`)
- Runtime base: `php-nginx@8.4` (build on `php@8.4` + `nodejs@22`)
- Additional setups: `worker` — runs `php artisan queue:work --sleep=3 --tries=3` on a separate service (no HTTP, no healthCheck, `initCommands` caches config on every start)

## Zerops dev (hybrid)

Runtime (`php-nginx`) auto-serves PHP changes immediately — edit `.blade.php` / `.php` and they take effect on the next request.

**Vite assets build ONCE per service lifetime** via dev `initCommands` (`zsc execOnce vite-build-v1 -- npm run build`). First deploy produces `public/build/manifest.json` so the first email-link visit renders correctly. Subsequent deploys skip the build — manifest is reused.

**For live editing, run HMR over SSH:** `ssh appdev 'cd /var/www && nohup npm run dev > /tmp/vite.log 2>&1 &'`. This writes `public/hot`; Laravel's `@vite()` routes through the dev server and ignores the manifest. Containers restart on every `zerops_deploy` (hot file wiped), so rerun after each redeploy.

**To refresh the baked manifest manually** (e.g. after a JS/CSS push, with no HMR session running): `ssh appdev 'cd /var/www && npm run build'`. Usually not needed — HMR override means a stale manifest is invisible during real dev work.

**Do NOT add `npm run build` to dev `buildCommands`** — it would add ~20–30 s to every push for no benefit; the initCommands path covers first-visit UX without taxing the shared builder.

**All platform operations (deploy, env / scaling / storage / domains) go through the Zerops development workflow via `zcp` MCP tools. Don't shell out to `zcli`.**

## Notes

- Dev runtime installs Node 22 via `prepareCommands` (`sudo -E zsc install nodejs@22`) — cached into the runtime image, not re-run on restart.
- `initCommands` use `zsc execOnce` with two token styles: `${appVersionId}` / `${appVersionId}-scout` for per-deploy steps (`migrate`, `scout:import`) and stable tokens (`seed-v1`, `vite-build-v1`) for once-per-service-lifetime steps (`db:seed`, `npm run build`). Prod additionally runs `config:cache`, `route:cache`, `view:cache`; dev skips these so config changes take effect immediately.
- Use `predis/predis` (`REDIS_CLIENT: predis`) — `php-nginx@8.4` does not include the `phpredis` C extension.
- S3 requires `AWS_USE_PATH_STYLE_ENDPOINT: "true"` — MinIO does not support virtual-hosted bucket URLs.
- `APP_KEY` is NOT set in `zerops.yaml envVariables` — set it at the Zerops project level so `prod`, `dev`, and `worker` all share the same key.
