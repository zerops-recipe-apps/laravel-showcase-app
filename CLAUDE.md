# laravel-showcase-app

Laravel 13 recipe on Zerops with PostgreSQL, Valkey (Redis), S3-compatible object storage, Meilisearch, and a background queue worker — three setups: `prod` (HTTP), `dev` (HTTP + SSH live edit), `worker` (no HTTP).

## Zerops service facts

- HTTP port: `80` (document root `public/`)
- Siblings: `db` (PostgreSQL), `redis` (Valkey), `storage` (S3/MinIO), `search` (Meilisearch) — env vars follow the `${hostname_*}` pattern (`DB_*`, `REDIS_*`, `AWS_*`, `MEILISEARCH_HOST/KEY`)
- Runtime base: `php-nginx@8.4` (build on `php@8.4` + `nodejs@22`)
- Additional setups: `worker` — runs `php artisan queue:work --sleep=3 --tries=3` on a separate service (no HTTP, no healthCheck, `initCommands` caches config on every start)

## Zerops dev (hybrid)

Runtime (`php-nginx`) auto-serves PHP changes immediately — edit `.blade.php` / `.php` and they take effect on the next request.

**The `dev` setup intentionally skips `npm run build` in `buildCommands`** to keep the HMR workflow (`npm run dev`) fast. Consequence: `public/build/manifest.json` does NOT exist after a fresh deploy, so any view using `@vite(...)` throws HTTP 500 `Vite manifest not found` on the first request.

**After every `zerops_deploy` on this service and BEFORE running `zerops_verify`**, do ONE of:

- **One-shot build** — `ssh appdev 'cd /var/www && npm run build'`. Creates the manifest; survives until the next deploy rsyncs the build tree back in.
- **Long-running dev server (HMR)** — `ssh appdev 'cd /var/www && nohup npm run dev > /tmp/vite.log 2>&1 &'`. Drops `public/build/hot`; Laravel's `@vite` helper routes asset URLs to the dev server. Containers restart on every `zerops_deploy`, so rerun after each redeploy.

**Do NOT add `npm run build` to dev `buildCommands`** — it adds ~20–30 s to every push and defeats the HMR-first design.

**All platform operations (deploy, env / scaling / storage / domains) go through the Zerops development workflow via `zcp` MCP tools. Don't shell out to `zcli`.**

## Notes

- Dev runtime installs Node 22 via `prepareCommands` (`sudo -E zsc install nodejs@22`) — cached into the runtime image, not re-run on restart.
- `initCommands` in prod AND dev run `migrate --force`, `db:seed --force`, and `scout:import` under `zsc execOnce ${appVersionId} --retryUntilSuccessful` — runs once per deploy across all containers. Prod additionally runs `config:cache`, `route:cache`, `view:cache`; dev skips these so config changes take effect immediately.
- Use `predis/predis` (`REDIS_CLIENT: predis`) — `php-nginx@8.4` does not include the `phpredis` C extension.
- S3 requires `AWS_USE_PATH_STYLE_ENDPOINT: "true"` — MinIO does not support virtual-hosted bucket URLs.
- `APP_KEY` is NOT set in `zerops.yaml envVariables` — set it at the Zerops project level so `prod`, `dev`, and `worker` all share the same key.
