# Contributing

1. Clone repository to your computer.

```bash
git clone https://github.com/stickleapp/core.git
```

2. Install php dependencies:

```bash
composer install
```

3. Install npm dependencis:

```bash
npm install
```

## Local Development

This is an installable package, not an application. [Orchestra
Testbench](https://packages.tools/testbench) boots a real Laravel app from
`testbench.yaml` with `workbench/` supplying the app layer (models, segments,
routes, seeders). Configuration comes from the `env:` block in `testbench.yaml`
— there is no `.env` file.

**Requires PostgreSQL.** The committed config expects a `stickle-core` database
on `127.0.0.1:5432` as user `root`.

The shared configuration lives in `testbench.yaml.dist`, which is committed —
a fresh clone needs no setup. Testbench resolves `testbench.yaml` →
`testbench.yaml.example` → `testbench.yaml.dist` and uses the first it finds, so
to change anything locally, copy the `.dist` to `testbench.yaml` and edit that.
It is gitignored and takes precedence.

### First run

```bash
composer install && npm install

# Build the package's assets and publish them into the workbench app.
npm run build:publish

# Create the schema.
vendor/bin/testbench migrate:fresh

# Migrations create partitioned PARENT tables but no partitions. Seeders backfill
# ~25 days of history, so partitions must cover that range or inserts fail with
# "no partition of relation found for row".
for t in requests requests_rollup_1min requests_rollup_5min requests_rollup_1hr \
         requests_rollup_1day sessions_rollup_1day model_attribute_audit \
         segment_statistics model_relationship_statistics; do
  vendor/bin/testbench stickle:create-partitions "stc_$t" public week "$(date -v-5w +%Y-%m-%d)" 7
done

vendor/bin/testbench db:seed --class='\Workbench\Database\Seeders\DatabaseSeeder'
```

### Day to day

```bash
vendor/bin/testbench serve
```

> **`composer dev`, `composer serve`, and `composer start` wipe your database.**
> Each runs `workbench:build` first, which `testbench.yaml` defines as
> `asset-publish, db-wipe, migrate-fresh`. Use them only when you want a full
> rebuild. `vendor/bin/testbench serve` is non-destructive.

Any artisan command works through the same binary — `vendor/bin/testbench
migrate`, `vendor/bin/testbench tinker`, and so on.

### Working on CSS

Either run the Vite dev server for HMR:

```bash
npm run dev
```

or rebuild and republish after each change:

```bash
npm run build:publish
```

### Seed data goes stale

Every seeder generates data relative to `now()`, and the UI charts query a
rolling 30-day window. A database seeded more than a month ago renders empty
charts even though the tables hold plenty of rows. Re-run the first-run sequence
above when that happens.

## How Assets Work

Understanding this matters, because getting it wrong produces 404s that only
appear after deploying.

| | Path | Purpose |
| --- | --- | --- |
| `vite build` | `public/build/` | The artifact that ships. **Committed to the repo.** |
| `vite serve` | testbench public | HMR only — writes the `hot` file where the workbench app finds it |

`CoreServiceProvider` publishes `public/build` to
`public/vendor/stickleapp/core` under the `package-assets` tag. A dedicated
`Illuminate\Foundation\Vite` instance (bound as `stickle.vite`) resolves assets
from there:

```php
$this->app->singleton('stickle.vite', fn () => (new Vite)
    ->useBuildDirectory('vendor/stickleapp/core')
    ->useHotFile(public_path('vendor/stickleapp/core/hot'))
    ->withEntryPoints(['resources/css/app.css', 'resources/js/app.js']));
```

Layouts render it with `{{ app('stickle.vite') }}`, which emits the preload,
stylesheet, and module tags in one call. It is a separate instance from the
application's own Vite, so a host app's `@vite` is unaffected.

There is deliberately **no environment branching**. The `hot` file decides:
present means HMR, absent means built assets. `testbench.yaml` lists
`package-assets` in its `assets:` block so the workbench resolves assets through
the identical path an installed app uses — a missing or stale publish fails
locally instead of in production.

Because the hot file and `hotFile` in `vite.config.js` must agree, change them
together.

### Releasing

Rebuild and commit the assets whenever CSS or JS changes:

```bash
npm run build
git add public/build
```

## Testing

```bash
composer test        # Pest
composer analyse     # PHPStan level 8
composer lint        # Pint + PHPStan
composer verify      # Everything, plus npm build
```

Tests run against a real PostgreSQL database, migrating up in `setUp` and rolling
back in `tearDown`. A test that errors mid-run can leave residue that breaks the
next run's `setUp`; re-run clean to confirm a genuine failure.

## Editing Documentation

```bash
npm run docs:dev
```
