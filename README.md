# Laravel Showcase Recipe App

<!-- #ZEROPS_EXTRACT_START:intro# -->

A full-featured Laravel application with PostgreSQL, Valkey (Redis), S3 object storage, Meilisearch full-text search, and a background queue worker — demonstrating all major integrations on [Zerops](https://zerops.io) platform.
Used within [Laravel Showcase recipe](https://app.zerops.io/recipes/laravel-showcase) for [Zerops](https://zerops.io) platform.

<!-- #ZEROPS_EXTRACT_END:intro# -->

⬇️ **Full recipe page and deploy with one-click**

[![Deploy on Zerops](https://github.com/zeropsio/recipe-shared-assets/blob/main/deploy-button/light/deploy-button.svg)](https://app.zerops.io/recipes/laravel-showcase?environment=small-production)

![laravel cover](https://github.com/zeropsio/recipe-shared-assets/blob/main/covers/svg/cover-laravel.svg)

## Integration Guide

<!-- #ZEROPS_EXTRACT_START:integration-guide# -->

### 1. Adding `zerops.yaml`

The main configuration file — place at repository root. It tells Zerops how to build, deploy and run your app.

```yaml
zerops:
  # Production — optimized build, compiled assets, framework caches,
  # full service connectivity (DB, Redis, S3, Meilisearch).
  - setup: prod
    build:
      # Multi-base build: PHP for Composer, Node for Vite asset
      # compilation. Both runtimes are fully available on PATH
      # during the build — no manual install needed.
      base:
        - php@8.4
        - nodejs@22
      buildCommands:
        # Production Composer install — no dev packages, classmap
        # optimized for faster autoloading in production.
        - composer install --no-dev --optimize-autoloader
        # Vite compiles Tailwind CSS and JS into content-hashed
        # bundles in public/build/. These static assets are all
        # the runtime container needs from the Node side.
        - npm install
        - npm run build
      deployFiles:
        # List each directory explicitly — deploying ./ would
        # ship node_modules, .env.example, and other build-only
        # artifacts the runtime container doesn't need.
        - app
        - bootstrap
        - config
        - database
        - public
        - resources/views
        - routes
        - storage
        - vendor
        - artisan
        - composer.json
      # Cache vendor/ and node_modules/ between builds so
      # Composer and npm skip redundant network fetches.
      cache:
        - vendor
        - node_modules

    # Readiness check gates the traffic switch — new containers
    # must answer HTTP 200 before the L7 balancer routes to them.
    # This enables zero-downtime deploys.
    deploy:
      readinessCheck:
        httpGet:
          port: 80
          path: /health

    run:
      # php-nginx serves via Nginx + PHP-FPM — no explicit start
      # command needed; the base image handles both processes.
      base: php-nginx@8.4
      # Nginx serves static files from public/ and proxies PHP
      # requests to FPM. Laravel expects this as its web root.
      documentRoot: public
      # Config, route, and view caches MUST be built at runtime.
      # Build runs at /build/source/ but the app serves from
      # /var/www/ — caching during build bakes wrong paths.
      #
      # Migrations run exactly once per deploy via zsc execOnce,
      # regardless of how many containers start in parallel.
      # Seeder populates sample data on first deploy so the
      # dashboard shows real records immediately.
      # Scout import rebuilds the Meilisearch index from DB data
      # after seeding — the safety net for when auto-indexing
      # fires zero events (records already exist from prior deploy).
      initCommands:
        - zsc execOnce ${appVersionId} --retryUntilSuccessful -- php artisan migrate --force
        - zsc execOnce ${appVersionId} --retryUntilSuccessful -- php artisan db:seed --force
        - zsc execOnce ${appVersionId} --retryUntilSuccessful -- php artisan scout:import "App\\Models\\Article"
        - php artisan config:cache
        - php artisan route:cache
        - php artisan view:cache
      # Health check restarts unresponsive containers after the
      # 5-minute retry window expires — keeps production alive.
      healthCheck:
        httpGet:
          port: 80
          path: /health
      envVariables:
        APP_NAME: "Laravel Zerops"
        # Production mode — stack traces hidden, error pages
        # generic, optimizations enabled.
        APP_ENV: production
        APP_DEBUG: "false"
        # APP_URL drives absolute URL generation for redirects,
        # signed URLs, mail links, and CSRF origin validation.
        # zeropsSubdomain is the platform-injected HTTPS URL.
        APP_URL: ${zeropsSubdomain}
        # Stderr logging sends output to Zerops runtime log
        # viewer — no log files to manage or rotate.
        LOG_CHANNEL: stderr
        LOG_LEVEL: warning
        # Cross-service references resolve at deploy time.
        # Pattern: ${hostname_varname} maps to the db service's
        # auto-generated credentials.
        DB_CONNECTION: pgsql
        DB_HOST: ${db_hostname}
        DB_PORT: ${db_port}
        DB_DATABASE: ${db_dbName}
        DB_USERNAME: ${db_user}
        DB_PASSWORD: ${db_password}
        # Valkey (Redis-compatible) for cache, sessions, and
        # queues — single service handles all three concerns.
        # predis client is a pure-PHP Redis client that needs
        # no compiled extension.
        REDIS_CLIENT: predis
        REDIS_HOST: ${redis_hostname}
        REDIS_PORT: ${redis_port}
        SESSION_DRIVER: redis
        CACHE_STORE: redis
        QUEUE_CONNECTION: redis
        # S3-compatible object storage backed by MinIO.
        # forcePathStyle is mandatory — MinIO does not support
        # virtual-hosted bucket addressing.
        FILESYSTEM_DISK: s3
        AWS_ACCESS_KEY_ID: ${storage_accessKeyId}
        AWS_SECRET_ACCESS_KEY: ${storage_secretAccessKey}
        AWS_DEFAULT_REGION: us-east-1
        AWS_BUCKET: ${storage_bucketName}
        AWS_ENDPOINT: ${storage_apiUrl}
        AWS_USE_PATH_STYLE_ENDPOINT: "true"
        # Meilisearch for full-text search via Laravel Scout.
        # The host uses internal HTTP — SSL is terminated at
        # the L7 balancer, not between services.
        SCOUT_DRIVER: meilisearch
        MEILISEARCH_HOST: http://${search_hostname}:${search_port}
        MEILISEARCH_KEY: ${search_masterKey}
        # Mail set to log driver — no external SMTP configured.
        # Replace with real SMTP credentials for production use.
        MAIL_MAILER: log

  # Dev — full source deployed for live editing via SSHFS.
  # PHP-FPM serves requests immediately; edit files in /var/www
  # and changes take effect on the next request — no restart.
  - setup: dev
    build:
      # Same multi-base as prod — both PHP and Node available
      # during the build so Composer and npm can run.
      base:
        - php@8.4
        - nodejs@22
      buildCommands:
        # Full Composer install with dev packages — testing and
        # debugging tools available over SSH.
        - composer install
        # Pre-populate node_modules so the developer can run
        # npm run dev (Vite HMR) immediately after SSH-ing in
        # without waiting for another install.
        - npm install
      # Deploy the entire working directory — source files,
      # vendor/, node_modules/, and zerops.yaml so zcli push
      # works from the dev container.
      deployFiles:
        - ./
      cache:
        - vendor
        - node_modules

    run:
      base: php-nginx@8.4
      documentRoot: public
      # Install Node on the runtime container so the developer
      # can run Vite dev server (npm run dev) over SSH. This
      # runs once and is cached into the runtime image — not
      # re-executed on every container restart.
      prepareCommands:
        - sudo -E zsc install nodejs@22
      # Migration + seed runs once per deploy — DB is ready
      # when the SSH session starts. No cache warming in dev
      # — we want config changes to take effect immediately.
      initCommands:
        - zsc execOnce ${appVersionId} --retryUntilSuccessful -- php artisan migrate --force
        - zsc execOnce ${appVersionId} --retryUntilSuccessful -- php artisan db:seed --force
        - zsc execOnce ${appVersionId} --retryUntilSuccessful -- php artisan scout:import "App\\Models\\Article"
      envVariables:
        APP_NAME: "Laravel Zerops"
        # Dev mode — detailed error pages with stack traces,
        # no config caching, verbose logging for debugging.
        APP_ENV: local
        APP_DEBUG: "true"
        APP_URL: ${zeropsSubdomain}
        # Debug-level stderr logging surfaces all framework
        # events in the Zerops log viewer.
        LOG_CHANNEL: stderr
        LOG_LEVEL: debug
        # Same service wiring as prod — only mode flags differ.
        DB_CONNECTION: pgsql
        DB_HOST: ${db_hostname}
        DB_PORT: ${db_port}
        DB_DATABASE: ${db_dbName}
        DB_USERNAME: ${db_user}
        DB_PASSWORD: ${db_password}
        REDIS_CLIENT: predis
        REDIS_HOST: ${redis_hostname}
        REDIS_PORT: ${redis_port}
        SESSION_DRIVER: redis
        CACHE_STORE: redis
        QUEUE_CONNECTION: redis
        FILESYSTEM_DISK: s3
        AWS_ACCESS_KEY_ID: ${storage_accessKeyId}
        AWS_SECRET_ACCESS_KEY: ${storage_secretAccessKey}
        AWS_DEFAULT_REGION: us-east-1
        AWS_BUCKET: ${storage_bucketName}
        AWS_ENDPOINT: ${storage_apiUrl}
        AWS_USE_PATH_STYLE_ENDPOINT: "true"
        SCOUT_DRIVER: meilisearch
        MEILISEARCH_HOST: http://${search_hostname}:${search_port}
        MEILISEARCH_KEY: ${search_masterKey}
        MAIL_MAILER: log

  # Worker — background job processor consuming from Redis queue.
  # Same codebase as the app, different entry point. No HTTP
  # traffic — no healthCheck, readinessCheck, or documentRoot.
  - setup: worker
    build:
      # Worker only needs PHP — no asset compilation. The queue
      # runner processes jobs, not HTTP requests with CSS/JS.
      base:
        - php@8.4
      buildCommands:
        - composer install --no-dev --optimize-autoloader
      deployFiles:
        - app
        - bootstrap
        - config
        - database
        - public
        - resources/views
        - routes
        - storage
        - vendor
        - artisan
        - composer.json
      cache:
        - vendor

    run:
      # php-nginx base provides the PHP runtime. The queue:work
      # command runs as the foreground process instead of FPM.
      base: php-nginx@8.4
      # artisan queue:work processes jobs from the Redis queue.
      # --sleep=3 polls every 3s when idle, --tries=3 retries
      # failed jobs before marking them as permanently failed.
      start: php artisan queue:work --sleep=3 --tries=3
      # Cache framework config on every container start so the
      # worker resolves env vars and service references correctly.
      initCommands:
        - php artisan config:cache
      envVariables:
        APP_NAME: "Laravel Zerops"
        APP_ENV: production
        APP_DEBUG: "false"
        APP_URL: ${zeropsSubdomain}
        LOG_CHANNEL: stderr
        LOG_LEVEL: warning
        DB_CONNECTION: pgsql
        DB_HOST: ${db_hostname}
        DB_PORT: ${db_port}
        DB_DATABASE: ${db_dbName}
        DB_USERNAME: ${db_user}
        DB_PASSWORD: ${db_password}
        REDIS_CLIENT: predis
        REDIS_HOST: ${redis_hostname}
        REDIS_PORT: ${redis_port}
        # Worker shares the same Redis-backed drivers as the app.
        # Sessions are configured but unused by the CLI process.
        SESSION_DRIVER: redis
        CACHE_STORE: redis
        QUEUE_CONNECTION: redis
        FILESYSTEM_DISK: s3
        AWS_ACCESS_KEY_ID: ${storage_accessKeyId}
        AWS_SECRET_ACCESS_KEY: ${storage_secretAccessKey}
        AWS_DEFAULT_REGION: us-east-1
        AWS_BUCKET: ${storage_bucketName}
        AWS_ENDPOINT: ${storage_apiUrl}
        AWS_USE_PATH_STYLE_ENDPOINT: "true"
        SCOUT_DRIVER: meilisearch
        MEILISEARCH_HOST: http://${search_hostname}:${search_port}
        MEILISEARCH_KEY: ${search_masterKey}
        MAIL_MAILER: log
```

### 2. Trust the reverse proxy

Zerops terminates SSL at its L7 balancer and forwards requests via reverse proxy. Without trusting the proxy, Laravel rejects CSRF tokens and generates `http://` URLs instead of `https://`. In `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->trustProxies(at: '*');
})
```

### 3. Configure Redis client

Laravel defaults to the `phpredis` C extension. On Zerops, the `predis` pure-PHP client avoids needing a compiled extension. Install via Composer and set `REDIS_CLIENT=predis` in your environment:

```bash
composer require predis/predis
```

### 4. Configure S3 object storage

Install the S3 Flysystem adapter and set `FILESYSTEM_DISK=s3` with the Zerops object storage credentials. Path-style endpoints are mandatory for the MinIO-backed storage:

```bash
composer require league/flysystem-aws-s3-v3
```

### 5. Configure Meilisearch search

Install Laravel Scout with the Meilisearch driver for full-text search. Add the `Searchable` trait to models you want indexed:

```bash
composer require laravel/scout meilisearch/meilisearch-php
```
<!-- #ZEROPS_EXTRACT_END:integration-guide# -->

<!-- #ZEROPS_EXTRACT_START:knowledge-base# -->

### Gotchas

- **No `.env` file** — Zerops injects environment variables as OS env vars. Creating a `.env` file with empty values shadows the OS vars, causing `env()` to return `null` for every key that appears in `.env` even if the platform has a value set.
- **Cache commands in `initCommands`, not `buildCommands`** — `config:cache`, `route:cache`, and `view:cache` bake absolute paths into their cached files. The build container runs at `/build/source/` while the runtime serves from `/var/www/`. Caching during build produces paths like `/build/source/storage/...` that crash at runtime with "directory not found."
- **`APP_KEY` is project-level** — Laravel's encryption key must be shared across all services that read the same database (app + worker both need the same key for sessions and encrypted columns). Set it once at project level in Zerops; do not add it per-service or in `zerops.yaml envVariables`.
- **PDO PostgreSQL extension** — The `php-nginx` base image includes `pdo_pgsql` out of the box. No `prepareCommands` or `apk add` needed for PostgreSQL connectivity.
- **Predis over phpredis** — The `php-nginx` base image does not include the `phpredis` C extension. Use the `predis/predis` Composer package and set `REDIS_CLIENT=predis` to avoid "class Redis not found" errors.
- **Object storage requires path-style** — Zerops object storage uses MinIO, which requires `AWS_USE_PATH_STYLE_ENDPOINT=true`. Without it, the SDK attempts virtual-hosted bucket URLs that MinIO cannot resolve.
- **Vite manifest missing on dev after fresh deploy** — the `dev` setup intentionally omits `npm run build` from `buildCommands` so the HMR workflow (`npm run dev` via SSH) stays fast. Any view rendering `@vite(...)` therefore 500s with `Vite manifest not found at: /var/www/public/build/manifest.json` on the first request after a `zerops_deploy`. Fix: run `ssh appdev 'cd /var/www && npm run build'` once after the deploy and before `zerops_verify` — SSHFS propagates the manifest into the container without a redeploy. For iterative work, `ssh appdev 'cd /var/www && nohup npm run dev > /tmp/vite.log 2>&1 &'` drops `public/build/hot` and Laravel routes asset URLs to the dev server. **Do NOT add `npm run build` to dev `buildCommands`** — it adds ~20–30 s to every `zcli push` and defeats the HMR-first design.
<!-- #ZEROPS_EXTRACT_END:knowledge-base# -->
