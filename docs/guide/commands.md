# Artisan Commands

Every command Stickle registers, what it does, and whether the package schedules it
for you. Generated against the installed package — if a command is not listed here,
it does not exist.

Run `php artisan list stickle` to see the same list in your own application.

## Scheduled for you

These run automatically once `schedule:run` is in your crontab. See
[Deployment](/guide/deployment#scheduled-tasks).

| Command | Runs | Purpose |
| --- | --- | --- |
| `stickle:rollup-requests {grain=all}` | `1min` every minute, `5min` every 5, `1hr` every 15, `1day` hourly | Aggregates rows from `stc_requests` into the rollup tables. The `1day` grain is the one `eventCount` and `requestCount` read. |
| `stickle:rollup-sessions {days_ago=7}` | hourly | Counts distinct sessions per model per day. |
| `stickle:export-segments {namespace} {limit}` | every 5 min | Recalculates segment membership. |
| `stickle:record-model-attributes {namespace} {limit?}` | every 5 min | Stores a point-in-time copy of tracked attributes. |
| `stickle:record-segment-statistics {segmentId?} {limit?}` | every 5 min | Stores aggregates per segment. |
| `stickle:record-model-relationship-statistics {limit?}` | every 5 min | Stores aggregates across declared relationships. |
| `stickle:process-segment-events` | every 5 min | Turns `model_segment` audit rows into `ModelEnteredSegment` / `ModelExitedSegment` events. |
| `stickle:create-partitions` | twice daily | Creates upcoming partitions for each partitioned table. |
| `stickle:drop-partitions` | twice daily | Drops partitions past their retention window. **Deletes data.** |

::: tip The five-minute cadence is a floor, not the refresh rate
The four `record-*` and `export-*` commands compare a last-recorded timestamp
against their own key in the `schedule` block of `config/stickle.php` and skip
anything not yet due. Lower those values to refresh more often; the cron cadence
stays the same.
:::

## Run by hand

| Command | Purpose |
| --- | --- |
| `stickle:install` | Interactive setup. Writes `.env`, publishes config, optionally installs Reverb, runs migrations, creates the first partitions. |
| `stickle:dev-schedule` | Runs the scheduled commands once, in order. For local development. |

## Partition management

Both partition commands take the same arguments:

```bash
php artisan stickle:create-partitions {table} {schema} {interval} {period_start} {interval_count?}
php artisan stickle:drop-partitions   {table} {schema} {interval} {period_start}
```

`create-partitions` always covers the current period as well as the range you ask
for, so a first run cannot leave "now" without a partition.

`drop-partitions` removes every partition ending before `period_start`. It is the
only Stickle command that destroys data — check the value you pass.

```bash
# create 7 weekly partitions from 5 weeks ago
php artisan stickle:create-partitions stc_requests public week '2026-07-22' 7

# drop anything older than a year
php artisan stickle:drop-partitions stc_requests public week '2025-08-26'
```

## Rollup grains

`stickle:rollup-requests` takes a grain: `1min`, `5min`, `1hr`, `1day`, or `all`.

Each grain keeps its own bookmark (`last_aggregated_id` in `stc_rollups`) against the
shared `stc_requests` sequence, so the grains are independent. Running one more often
makes its series fresher; a missed run batches into the next one rather than losing
rows.

Bucket size does not dictate run frequency. A run aggregates whatever is new into the
correct bucket, so a less frequent grain is staler, never wrong.

::: warning A grain that has never run starts from the beginning
The bookmark defaults to `0`, so the first run of a grain aggregates **every row ever
written**, and needs rollup partitions covering that whole history or it fails with
`no partition of relation ... found for row`. To start a grain from now instead:

```sql
UPDATE stc_rollups
SET last_aggregated_id = (SELECT coalesce(max(id), 0) FROM stc_requests)
WHERE name = 'stc_requests_rollup_1min';
```
:::
