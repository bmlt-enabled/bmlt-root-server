# Virtual Aggregator (fork)

A fork of BMLT Server that runs the built-in aggregator in a **virtual-only** mode, with
two extra enrichment passes on import. It reuses the upstream aggregator end-to-end
(`aggregator:ImportRootServers` → `MeetingRepository::import()`); every addition is gated
behind a config flag that **defaults off**, so with the flags unset the code behaves exactly
like upstream and stays easy to rebase on `main`.

## What it does

1. **Virtual + hybrid only** — imports only meetings with `venue_type` 2 (virtual) or 3 (hybrid);
   in-person (1) meetings are skipped, and any already stored get pruned on the next import.
2. **Timezone backfill** — when a source meeting has no `time_zone`, derive an IANA zone from its
   coordinates (pure PHP, using the bundled tzdata; nearest zone by representative coordinates).
   Optional address→coordinates fallback for coordinate-less meetings.
3. **Locale backfill** — when a source meeting has no `lang_enum`, use the source root server's
   default language (`nativeLang` from its `GetServerInfo`).

## Configuration

All flags live in `src/config/aggregator.php` and read from env. Defaults reproduce upstream.

| Env var | Default | Effect |
|---|---|---|
| `AGGREGATOR_VIRTUAL_ONLY` | `false` | Import only venue types 2 and 3. |
| `AGGREGATOR_GEOCODE_TIMEZONES` | `false` | Backfill missing `time_zone` from coordinates. |
| `AGGREGATOR_INFER_LOCALE` | `false` | Backfill missing `lang_enum` from the source server default. |
| `AGGREGATOR_GEOCODE_ADDRESSES` | `false` | (Optional) geocode location text → coords when no lat/long, then resolve the zone. |
| `AGGREGATOR_GEOCODER_URL` | Nominatim `/search` | Geocoder endpoint for the address fallback. |
| `AGGREGATOR_GEOCODER_UA` | `bmlt-virtual-aggregator` | User-Agent sent to the geocoder. |

These are in addition to the stock `AGGREGATOR_MODE_ENABLED=true` that turns on aggregator mode.

## Code layout

New, fork-owned files (upstream never touches these):

- `src/app/Aggregator/TimezoneResolver.php` — nearest-IANA-zone from lat/long via
  `DateTimeZone::getLocation()` (no extension, no external data, no composer dependency).
  Memoized; optional `AddressGeocoder` fallback.
- `src/app/Aggregator/LocaleResolver.php` — reads `RootServer.server_info.nativeLang`
  (falls back to the first of `langs`), memoized per root server.
- `src/app/Aggregator/AddressGeocoder.php` — optional Nominatim lookup, cached by address.
- `src/tests/Feature/Aggregator/VirtualAggregatorTest.php` — covers the filter, both resolvers,
  and an end-to-end import.

Minimal, config-gated edits to shared files:

- `src/config/aggregator.php` — the flags above.
- `src/app/Repositories/MeetingRepository.php`:
  - `import()` — filters `$externalObjects` to venue 2/3 when `virtual_only` is on (before the
    source-id diff, so stale in-person rows are cleaned up).
  - `externalMeetingToValuesArray()` — `time_zone` / `lang_enum` fall back to the resolvers.
  - `create()` — respects an explicitly provided `lang_enum` instead of always forcing
    `App::currentLocale()` (admin creates pass none, so their behavior is unchanged).

## Enabling / running imports

Aggregator imports run via the Artisan command (there is no in-app scheduler):

```bash
php artisan aggregator:InitializeDatabase   # first run only, sets up the DB for aggregator mode
php artisan aggregator:ImportRootServers     # pulls every root server; run on a cron/loop
```

With the flags on, imports become virtual-only and backfill timezone + locale.

## Querying the data

The public query API is unchanged (`/main_server/client_interface/json/?switcher=...`).

### The aggregator "required filter" gate

Upstream aggregator mode **requires** `GetSearchResults` to include at least one of:
`meeting id`, `services` (service body), `formats`, `root_server_ids`, `meeting_key`+`meeting_key_value`,
`lat`+`long`, or `page_size`+`page_num`. Without one it returns `[]` **before** querying — this
stops an unfiltered call from dumping every aggregated server at once.

`weekdays`, `venue_types`, and time-range params do **not** satisfy the gate on their own. So this
returns empty despite matching data:

```
?switcher=GetSearchResults&weekdays=1&venue_types[]=2&venue_types[]=3        # []
```

Add a qualifying filter — the simplest is pagination:

```
?switcher=GetSearchResults&weekdays=1&venue_types[]=2&venue_types[]=3&page_size=5000&page_num=1
```

### Filter by timezone

There is no dedicated timezone parameter, but `time_zone` is a main-table field, so the generic
field filter works — and `meeting_key`+`meeting_key_value` also satisfies the gate by itself:

```
# single zone
?switcher=GetSearchResults&meeting_key=time_zone&meeting_key_value=America/New_York

# combine with venue
?switcher=GetSearchResults&meeting_key=time_zone&meeting_key_value=America/New_York&venue_types[]=2&venue_types[]=3

# multiple zones (WHERE time_zone IN (...))
?switcher=GetSearchResults&meeting_key=time_zone&meeting_key_value[]=America/New_York&meeting_key_value[]=America/Chicago
```

Values are exact IANA names (e.g. `America/New_York`, `Europe/Dublin`, `Asia/Kathmandu`).

### Optional future change

To let bare `weekdays`/`venue_types` queries work without a qualifying filter, add a flag
(e.g. `AGGREGATOR_REQUIRE_SEARCH_FILTERS`, default `true`) and skip the gate in
`SwitcherController::getSearchResults` when it is `false`. Not currently implemented.

## Deployment

Deployed as a self-hosted Docker Compose stack (see `docker/docker-compose.prod.yml` for the
reference template). Services: `bmlt` (web), `importer` (loops `aggregator:ImportRootServers`),
`db` (MariaDB).

Enable the fork features by setting the flags on **both** the `bmlt` and `importer` services, e.g.
via a `docker-compose.override.yaml`:

```yaml
services:
  bmlt:
    environment:
      AGGREGATOR_VIRTUAL_ONLY: "true"
      AGGREGATOR_GEOCODE_TIMEZONES: "true"
      AGGREGATOR_INFER_LOCALE: "true"
  importer:
    image: bmltenabled/bmlt-server:virtual
    environment:
      AGGREGATOR_VIRTUAL_ONLY: "true"
      AGGREGATOR_GEOCODE_TIMEZONES: "true"
      AGGREGATOR_INFER_LOCALE: "true"
```

First-run bring-up:

```bash
docker compose up -d db                      # wait until healthy
docker compose up -d bmlt
docker compose exec -T bmlt php /var/www/html/main_server/artisan aggregator:InitializeDatabase
docker compose up -d                         # starts importer (imports immediately, then on interval)
```

Ship a new build: rebuild/push the image, then on the host
`docker compose pull && docker compose up -d`.

## Building the image

Prod vs. debug is selected by the `CI` env var (`ifeq ($(CI)x, x)` in the Makefile — empty = debug).
The prod `docker/Dockerfile` just unzips a prebuilt `build/bmlt-server.zip` into
`bmltenabled/bmlt-server-base`, so the real build (composer `--no-dev`, `npm ci`, asset download)
happens on the host via `make zip` first, and multi-arch builds are fast.

```bash
# single-arch, loaded locally
make clean
CI=1 make docker

# multi-arch to a registry (what CI does)
CI=1 IMAGE=<repo>/bmlt-server TAG=virtual make docker-push
```

Multi-arch **without** a remote registry — build to an OCI archive, then push with skopeo:

```bash
make clean && CI=1 make zip
docker buildx build --pull \
  --platform linux/amd64,linux/arm64/v8 --build-arg PHP_VERSION=8.3 \
  -f docker/Dockerfile . -t bmlt-server:virtual \
  --output type=oci,dest=build/bmlt-server-multiarch.tar
skopeo copy --all oci-archive:build/bmlt-server-multiarch.tar docker://docker.io/<repo>/bmlt-server:virtual
```

> `make clean` first, or `zip` reuses dev-container artifacts (dev deps + debug frontend) and you
> get a fake-prod image.

## Tradeoffs / notes

- **Timezone accuracy is city-level.** Nearest-zone-by-representative-coordinates can pick a
  neighbor right on a border (e.g. some UK coordinates resolve to `Europe/Dublin`). Fine for
  displaying a plausible wall-clock zone; not authoritative near borders.
- **`isEqual` churn.** `ExternalMeeting::isEqual()` compares `time_zone`/`lang_enum` against the
  source's (empty) values, so backfilled meetings re-`update()` on every import. It's perf-only
  (no `Change` rows in aggregator mode) and the resolver caches keep it cheap.
- **Coordinate-less meetings** get no timezone unless `AGGREGATOR_GEOCODE_ADDRESSES` is on (which
  adds an external, rate-limited geocoder dependency).

## Testing

```bash
# in the dev container
vendor/bin/phpunit tests/Feature/Aggregator/VirtualAggregatorTest.php
vendor/bin/phpunit tests/Feature/Aggregator            # full aggregator suite
```
