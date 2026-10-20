# Docker guide (DT Propix)

How the Docker Compose stack works, how to customize it locally, and how
logging is handled. Code-level logging rules live in `docs/architecture.md`
§6 — this file is the operational companion.

## Services

| Service     | Image                | What it does                                                        |
| ----------- | -------------------- | ------------------------------------------------------------------- |
| `app`       | built from `docker/php/Dockerfile` | PHP-FPM running the Laravel app; image includes Chromium and fonts for Browsershot PDFs |
| `nginx`     | `nginx:1.27-alpine`  | Web server; PHP via FastCGI to `app:9000`, serves `public/`         |
| `mysql`     | `mysql:8.4`          | Database (`propix`), data in the `mysql-data` volume, published on 3306 |
| `redis`     | `redis:7-alpine`     | Cache, sessions and queues, published on 6379                       |
| `queue`     | same image as `app`  | Queue worker (`php artisan queue:work --sleep=1 --tries=3`)         |
| `scheduler` | same image as `app`  | Cron replacement (`php artisan schedule:work`)                      |

Real-time features use page refresh and light polling by design (`docs/architecture.md`
§1), so there is no websocket service. All long-running services use Docker's
`json-file` log driver with rotation (see [Logging standard](#logging-standard)).

## Quick start

```sh
cp .env.example .env                                            # then edit .env
cp docker-compose.override.yml.example docker-compose.override.yml  # local overrides (git-ignored)
docker compose up -d
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

Then open http://localhost:8080.

## Override workflow

- `docker-compose.override.yml` is your local playground: change ports, add
  services, enable Mailpit/Xdebug — it is git-ignored, so edit it freely.
  Compose merges it automatically with `docker compose up -d`.
- To run production-like (base file only, no local overrides):

  ```sh
  docker compose -f docker-compose.yml up -d
  ```

- The committed template `docker-compose.override.yml.example` shows what is
  available: notes on moving the MySQL/Redis host ports (already published on
  3306/6379 by the base file), Mailpit for local mail, an Xdebug block, and a
  `tools` profile (`docker compose --profile tools up -d` → Adminer on :8081,
  RedisInsight on :5540).

## Common commands

```sh
docker compose logs -f app          # follow app logs (also: nginx, mysql, redis, queue, scheduler)
docker compose exec app bash        # shell into the app container
docker compose exec app php artisan queue:restart    # restart queue workers after code changes
docker compose exec app php artisan migrate --force # run migrations without confirmation
docker compose build --no-cache app # full rebuild of the PHP image (queue/scheduler share it)
docker compose up -d                # apply compose/Dockerfile changes
docker compose down                 # stop everything (data survives in volumes)
```

## Logging standard

### Docker side

- Every long-running service rotates its own logs: `json-file` driver,
  `max-size: 10m`, `max-file: 3` (the `x-logging` anchor in
  `docker-compose.yml`). Containers can never fill the host disk.
- View logs with `docker compose logs -f <service>` (add `--tail=100` to skip
  history). Nginx access and error logs appear under `docker compose logs -f nginx`.

### App side

- Laravel writes to `stderr` (picked up by `docker compose logs`) and to daily
  files in `storage/logs/laravel-YYYY-MM-DD.log`. Configure in `.env`:

  ```env
  LOG_STACK=stderr,daily
  LOG_LEVEL=debug        # use "info" in production
  LOG_DAILY_DAYS=14
  ```

- Always pass structured context as an array — never string interpolation —
  so entries stay searchable:

  ```php
  Log::warning('SMS gateway timeout', ['mobile_hash' => $h, 'attempt' => 2]);
  ```

- **Never log customer, financial or property-ownership data** (names, mobile
  numbers, NID, payment details, schedules) — log IDs, not the values. This is
  a hard rule; see `docs/architecture.md` §12 (Customer, Financial & Property
  Data Rules).
- Exceptions go through the framework exception logger (`report($e)` /
  `Log::error(...)` in one place) — never `var_dump`, `dd` or `error_log`.
- Domain audit events (bookings, cancellations, journal entries) go through
  spatie/laravel-activitylog, not the log files.
- Code-level rules: `docs/architecture.md` §6 (Logging).
