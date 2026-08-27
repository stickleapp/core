# Stickle MCP Server Specification

## Overview

**Goal:** Ship a first-party, read-only [MCP](https://modelcontextprotocol.io) server inside
`stickleapp/core` so AI clients (Claude Code, Claude Desktop, Cursor, ChatGPT, custom agents)
can explore a host application's customer analytics: tracked models, segments, attribute
history, events, and statistics.

**SDK:** [`laravel/mcp`](https://laravel.com/docs/12.x/mcp) — Laravel's official MCP server
package. It provides the `Server` / `Tool` / `Resource` / `Prompt` base classes, both
Streamable HTTP and stdio transports, schema builders, tool annotations, and test helpers.

**Posture:** Strictly read-only. Every tool queries existing Stickle tables or model metadata.
No tool ingests events, mutates segments, or writes anything. This is enforced in three
layers (see [Security](#security)).

### Non-goals

- Event ingestion over MCP (the `/track` REST endpoint already covers ingestion).
- Creating/editing segments over MCP. A future write-enabled server could add this behind a
  separate opt-in; it is explicitly out of scope here.
- Exposing arbitrary SQL or arbitrary Eloquent queries.

---

## Dependencies and compatibility

```
composer require laravel/mcp
```

- `laravel/mcp` requires Laravel 11+. The README already states Laravel 11+ / PHP 8.3+ even
  though `composer.json` still allows `illuminate/contracts ^10`. **Decision needed:** either
  drop the `^10` constraint (recommended — it matches the documented requirement), or make
  `laravel/mcp` a `suggest` dependency and register the server only when
  `class_exists(\Laravel\Mcp\Server::class)`. This spec assumes the hard dependency.
- Pin the version at implementation time (`^1.0` if released; otherwise the current `0.x`
  minor, since pre-1.0 minors may break).
- No new JS/CSS. No database changes — the server reads existing `stc_*` tables only.

---

## Architecture

### File layout

```
src/Mcp/
├── Servers/
│   └── StickleServer.php
├── Tools/
│   ├── Concerns/
│   │   ├── ResolvesTrackedModels.php    # ClassUtils + StickleEntity guard, shared errors
│   │   └── PaginatesResults.php         # page/per_page schema fragment + caps
│   ├── ListTrackedModels.php
│   ├── DescribeModel.php
│   ├── SearchModelRecords.php
│   ├── GetModelRecord.php
│   ├── GetModelAttributeHistory.php
│   ├── GetModelStatistics.php
│   ├── ListModelRelationshipRecords.php
│   ├── GetModelRelationshipStatistics.php
│   ├── ListSegments.php
│   ├── GetSegment.php
│   ├── ListSegmentMembers.php
│   ├── GetSegmentStatistics.php
│   ├── GetSegmentMembershipHistory.php
│   ├── ListEvents.php
│   ├── ListPageViews.php
│   └── GetActivityRollup.php
├── Resources/
│   ├── StickleOverviewResource.php
│   ├── ModelMetadataResource.php        # uri template: stickle://models/{modelClass}
│   └── SegmentDefinitionResource.php    # uri template: stickle://segments/{segmentId}
└── Prompts/
    ├── CustomerHealthCheckPrompt.php
    ├── SegmentReviewPrompt.php
    └── AnalyticsSummaryPrompt.php
```

### The server

```php
<?php

declare(strict_types=1);

namespace StickleApp\Core\Mcp\Servers;

use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('Stickle')]
#[Version('1.0.0')]
#[Instructions(<<<'MD'
    Read-only access to this application's Stickle customer analytics.

    Start with `list-tracked-models` and `list-segments` to discover what is
    tracked, then drill into records, statistics, and event activity. All
    date parameters are ISO-8601; all list tools are paginated. Attribute
    names must come from a model's tracked/observed attribute lists returned
    by `describe-model` — arbitrary column access is rejected.
    MD)]
class StickleServer extends Server
{
    protected array $tools = [ /* all Tools above */ ];

    protected array $resources = [ /* all Resources above */ ];

    protected array $prompts = [ /* all Prompts above */ ];
}
```

`#[Instructions]` matters: it is the only guidance many clients surface to the model, so it
must teach the discovery-first workflow (models → segments → drill-down).

### Registration and transports

Registered from `CoreServiceProvider::boot()` (packages cannot rely on the host publishing
`routes/ai.php`), guarded by config:

```php
use Laravel\Mcp\Facades\Mcp;

if (config('stickle.mcp.enabled')) {
    Mcp::web(config('stickle.mcp.routes.path', 'stickle/mcp'), StickleServer::class)
        ->middleware([
            ...config('stickle.mcp.routes.middleware', ['api', 'auth:sanctum']),
            'can:viewStickle',   // hardwired, exactly like routes/api.php
        ]);
}

if (config('stickle.mcp.local.enabled')) {
    Mcp::local('stickle', StickleServer::class);
}
```

- **Web transport (Streamable HTTP)** — the production path, at `POST /stickle/mcp`.
  Follows the existing package convention: transport middleware is configurable, but the
  `can:viewStickle` Gate is appended in code so no configuration value can remove it
  (mirrors the comment in `routes/api.php`).
- **Local transport (stdio)** — dev/debug convenience via `php artisan mcp:start stickle`
  and `php artisan mcp:inspector stickle`. Default **off**. stdio has no authenticated user,
  so the Gate would deny everything; when the local server is enabled, tools fall back to
  `Gate::allows('viewStickle')` with a null user, which the host app's Gate definition must
  explicitly permit (document this; it is the same "closed by default" behavior as the API).

### Authentication

- **Sanctum (default recommendation):** `auth:sanctum` in the default middleware stack; the
  host issues a token to whoever configures the MCP client.
- **OAuth (Passport):** documented alternative for interactive clients — host app calls
  `Mcp::oauthRoutes()` and swaps the middleware to `auth:api`. Stickle does not register
  OAuth routes itself; it only documents the recipe.
- Every tool *also* authorizes in `handle()` (defense in depth, and it produces a proper
  MCP error instead of an HTTP 403 mid-session):

```php
if (! Gate::forUser($request->user())->allows('viewStickle')) {
    return Response::error('Permission denied: viewStickle.');
}
```

---

## Tools

Conventions that apply to **every** tool:

- Annotated `#[IsReadOnly]` and `#[IsIdempotent]`.
- Arguments validated with `$request->validate([...])`; invalid input returns a
  `Response::error()` naming the offending argument.
- Model class arguments are class **basenames** (`User`, `Account`), resolved through
  `ClassUtils::tryResolveModelClass()` and rejected unless the class uses `StickleEntity` —
  identical guards to the REST controllers, extracted into `ResolvesTrackedModels`.
- Attribute arguments are checked against the union of the model's
  `stickleTrackedAttributes()` / `stickleObservedAttributes()` (and, where relevant, segment
  statistic attribute names). Unknown attributes are rejected, never interpolated.
- List tools accept `page` (default 1) and `per_page` (default 25, **max 100**); date-ranged
  tools accept `date_from` / `date_to` (ISO-8601, default last 30 days, **max span 366
  days**).
- Data tools return `Response::structured([...])` with a declared `outputSchema()`.
  Paginated payloads share one envelope: `{ data: [...], meta: { page, per_page, total,
  last_page } }`.
- Record payloads are serialized through the model's normal Eloquent serialization so
  `$hidden` casts apply, then filtered against `stickle.mcp.hiddenAttributes`.

### Discovery

| Tool | Arguments | Returns |
|---|---|---|
| `list-tracked-models` | — | Every model using `StickleEntity`: basename, FQCN-derived label, observed attributes, tracked attributes, relationships (from `StickleRelationshipMetadata`), record count. |
| `describe-model` | `model_class` | Deep metadata for one model: attribute metadata (`StickleAttributeMetadata` — name, description, data type, chart type, primary aggregate), relationship metadata, available event names seen for this class, the segments defined for it. |

### Segments

| Tool | Arguments | Returns |
|---|---|---|
| `list-segments` | `model_class?`, pagination | Segments (`stc_segments`) enriched with `StickleSegmentMetadata` (name, description, export interval) exactly as `SegmentsController` does; includes current member count. |
| `get-segment` | `segment_id` | One segment: metadata, model class, member count, `last_exported_at`, and a human-readable definition summary (`as_class` name, or rendered `as_json` filters). |
| `list-segment-members` | `segment_id`, pagination | Members via `Segment::objects()` (mirrors `SegmentModelsController`). |
| `get-segment-statistics` | `segment_id`, `attribute`, `date_from?`, `date_to?` | Time series + delta (start/end value, absolute and percentage change) from `stc_segment_statistics` — same shape `SegmentStatisticsController` returns. |
| `get-segment-membership-history` | `segment_id`, `object_uid?`, `date_from?`, `date_to?`, pagination | ENTER/EXIT rows from `stc_model_segment_audit`, for churn/flow analysis ("who entered At-Risk this week?"). |

### Model records and statistics

| Tool | Arguments | Returns |
|---|---|---|
| `search-model-records` | `model_class`, `search?`, `uid?`, pagination | Records matched by name ILIKE / primary key (mirrors `ModelsController`), each with `stickleLabel()` and `stickleUrl()`. |
| `get-model-record` | `model_class`, `uid` | One record plus its current Stickle attribute snapshot (`stc_model_attributes.data`), current segment memberships, and relationship names available for drill-down. |
| `get-model-attribute-history` | `model_class`, `uid`, `attribute`, `date_from?`, `date_to?` | Attribute audit time series + delta from `stc_model_attribute_audit` (generalizes `ModelAttributeAuditController`, which hardcodes 30 days). |
| `get-model-statistics` | `model_class`, `attribute` | Aggregates (avg/min/max/sum/count) of a tracked attribute across all records of the class (mirrors `ModelsStatisticsController`). |
| `list-model-relationship-records` | `model_class`, `uid`, `relationship`, pagination | Related records. **Tightened vs. the REST controller:** `relationship` must appear in the model's `stickleRelationships()` metadata, not merely `method_exists()`. |
| `get-model-relationship-statistics` | `model_class`, `uid`, `relationship`, `attribute`, `date_from?`, `date_to?` | Rollup time series + delta from `stc_model_relationship_statistics`. |

### Activity (events, page views, sessions)

| Tool | Arguments | Returns |
|---|---|---|
| `list-events` | `model_class?`, `object_uid?`, `event_name?`, `date_from?`, `date_to?`, pagination | `track`-type rows from `stc_requests` (newest first) with location data — the MCP twin of `RequestsController` filtered to `RequestType::TRACK`. |
| `list-page-views` | same as `list-events` minus `event_name`, plus `url?` | `page`-type rows. |
| `get-activity-rollup` | `grain` (`1min`\|`5min`\|`1hr`\|`1day`), `date_from?`, `date_to?`, `model_class?`, `object_uid?` | Aggregated counts from the `stc_requests_rollup_{grain}` tables — cheap time-series for "activity over time" charts without scanning the partitioned raw table. Grain is validated against the enum whitelist, never interpolated from raw input (same rule `AnalyticsRepositoryContract` documents). |

### Deferred (phase 3, behind its own config flag)

| Tool | Notes |
|---|---|
| `preview-filter` | Accepts a JSON filter definition (target from `FilterTarget`, test from `FilterTest`, arguments), builds a `Filters\Base` chain, and returns a member **count + sample (max 25)** via `scopeStickleWhere`. This is "segment preview as a tool" and is the most powerful/most sensitive surface — every target and test name is validated against the enums before touching `__callStatic`. Ship only after the core tools have soaked. |
| `list-sessions` / session rollups | Once session rollup querying stabilizes (`rollupSessions` exists but read surface is thin). |

---

## Resources

Resources give clients cheap, cacheable context without burning tool calls:

| Resource | URI | Content |
|---|---|---|
| `StickleOverviewResource` | `stickle://overview` | Runtime-generated markdown: which models are tracked, their attributes, defined segments, and a glossary of Stickle terms. Effectively "CLAUDE.md for this app's analytics". |
| `ModelMetadataResource` | `stickle://models/{modelClass}` (uses `HasUriTemplate` / `UriTemplate`) | Markdown rendering of `describe-model` for one model. |
| `SegmentDefinitionResource` | `stickle://segments/{segmentId}` | The segment's metadata and definition (its `toBuilder()` source location for as-code segments, or rendered filter JSON). |

All `mimeType: text/markdown`. Resource handlers apply the same Gate check as tools.

## Prompts

| Prompt | Arguments | Behavior |
|---|---|---|
| `CustomerHealthCheckPrompt` | `model_class`, `uid` | Guides the model through record snapshot → attribute deltas → recent events → segment membership, ending in a health assessment. |
| `SegmentReviewPrompt` | `segment_id` | Membership trend, statistic deltas, notable entries/exits. |
| `AnalyticsSummaryPrompt` | `period?` | Cross-segment weekly/monthly summary using rollups. |

---

## Configuration

New `mcp` block in `config/stickle.php`:

```php
'mcp' => [
    // Master switch for the HTTP (streamable) server.
    'enabled' => env('STICKLE_MCP_ENABLED', false),

    'routes' => [
        'path' => env('STICKLE_MCP_PATH', 'stickle/mcp'),

        /*
        | Transport plumbing + authentication only. Authorization is the
        | viewStickle Gate, which is appended in code and cannot be removed
        | here (same contract as routes.api.middleware).
        */
        'middleware' => ['api', 'auth:sanctum', 'throttle:60,1'],
    ],

    // stdio server for local development (`php artisan mcp:start stickle`).
    'local' => [
        'enabled' => env('STICKLE_MCP_LOCAL_ENABLED', false),
    ],

    // Attribute names stripped from every record payload, on top of $hidden.
    'hiddenAttributes' => ['email', 'phone'],

    'limits' => [
        'perPageMax' => 100,
        'dateRangeMaxDays' => 366,
    ],
],
```

Default **disabled**: an upgrade must never silently expose a new endpoint. `install:stickle`
gains a prompt ("Enable the Stickle MCP server?") that sets the env var and prints the
client configuration snippet.

---

## Security

Read-only is enforced in three layers:

1. **Code surface** — no tool, resource, or prompt handler calls anything but `SELECT`-path
   Eloquent/query-builder methods. An architecture test (Pest arch plugin, which the repo
   already uses) asserts nothing under `src/Mcp` references `create|save|update|delete|
   insert|truncate|statement`.
2. **Annotations** — every tool carries `#[IsReadOnly]` + `#[IsIdempotent]`, so well-behaved
   clients can auto-approve and audits can diff annotations against layer 1.
3. **Authorization** — `can:viewStickle` middleware on the route *and* a Gate check inside
   each handler. Absent Gate definition ⇒ denied (Laravel denies undefined abilities).

Additional controls:

- **Input hardening:** model classes resolve only within `stickle.namespaces.models` and
  must use `StickleEntity`; attributes/relationships/grains/event names validate against
  metadata or enums; nothing user-supplied is ever interpolated into SQL (parameter binding
  only, same as the controllers).
- **Data minimization:** Eloquent `$hidden` is respected (no `makeVisible` anywhere), then
  `stickle.mcp.hiddenAttributes` strips configured keys from records *and* attribute
  snapshots. Secrets never appear in resources (no config dumps).
- **Abuse limits:** `throttle:60,1` default, `per_page` ≤ 100, bounded date ranges, rollup
  tables preferred over raw partitioned tables for aggregates.
- **Optional read replica:** document `stickle.mcp` usage against a read-only DB connection
  as a belt-and-suspenders deployment option (no code change required — host can point the
  Stickle connection for MCP workers at a replica).

---

## Example tool (reference implementation shape)

```php
<?php

declare(strict_types=1);

namespace StickleApp\Core\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use StickleApp\Core\Mcp\Tools\Concerns\PaginatesResults;
use StickleApp\Core\Mcp\Tools\Concerns\ResolvesTrackedModels;

#[IsReadOnly]
#[IsIdempotent]
#[Description('List customer segments, optionally filtered by tracked model class.')]
class ListSegments extends Tool
{
    use PaginatesResults, ResolvesTrackedModels;

    public function handle(Request $request): Response
    {
        if ($denied = $this->authorize($request)) {
            return $denied;
        }

        $validated = $request->validate([
            'model_class' => ['sometimes', 'string', 'max:255'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);

        // Query mirrors SegmentsController@index, including metadata enrichment.

        return Response::structured($this->paginatedEnvelope($paginator));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'model_class' => $schema->string()
                ->description('Model basename, e.g. "User". Omit for all models.'),
            ...$this->paginationSchema($schema),
        ];
    }
}
```

Where practical, tool internals delegate to the same code paths the REST controllers use
(shared query logic extracted into support classes/actions as needed) so REST and MCP cannot
drift apart. Do not call controllers from tools; extract, then share.

---

## Testing

`laravel/mcp` ships first-class primitives that fit the existing Pest + workbench setup:

```php
it('lists segments for a model class', function () {
    seedWorkbenchSegments();

    StickleServer::actingAs($this->authorizedUser())
        ->tool(ListSegments::class, ['model_class' => 'User'])
        ->assertOk()
        ->assertSee('Active Users');
});
```

Required coverage:

- **Per tool:** happy path, unknown model class (404-style error), model without
  `StickleEntity`, disallowed attribute, pagination caps, date-range caps.
- **Authorization:** every tool/resource/prompt denies without `viewStickle`
  (loop over `StickleServer` registrations so a new tool cannot ship untested).
- **Read-only arch test** as described under Security.
- **Registration:** server route exists when `stickle.mcp.enabled`, absent when not;
  `can:viewStickle` present regardless of configured middleware.
- **Parity snapshots:** for the mirrored endpoints (segments, segment statistics,
  requests), assert MCP and REST return the same underlying rows for the same inputs.

Manual verification: `php artisan mcp:inspector stickle` against the workbench app
(`composer serve`), plus a Claude Code `.mcp.json` example in the docs.

---

## Documentation

- New guide page `docs/guide/mcp-server.md`: enabling, issuing a Sanctum token, client
  config snippets (Claude Code, Claude Desktop, Cursor), tool catalog, security model.
- `docs/guide/configuration.md`: document the `mcp` config block.
- README: add MCP to the feature list.

## Implementation phases

| Phase | Scope |
|---|---|
| **1 — Skeleton** | Dependency decision + composer change, config block, `StickleServer`, registration in `CoreServiceProvider`, auth/Gate wiring, `ListTrackedModels` + `ListSegments`, test harness + arch test, inspector smoke test. |
| **2 — Core read surface** | Remaining discovery/segment/model/activity tools, resources, prompts, shared concerns, installer prompt, docs. |
| **3 — Advanced** | `preview-filter` behind its own flag, session tools, parity snapshot suite, optional OAuth recipe docs. |

## Open questions

1. **Laravel 10:** drop `illuminate/* ^10` (README already says 11+) or gate MCP behind
   `class_exists`? Spec recommends dropping.
2. **Gate for stdio:** is "host must define `viewStickle` to allow `null` user" acceptable
   for the local dev server, or should local skip the Gate entirely (current lean: keep the
   Gate; dev annoyance beats a surprise hole).
3. **`hiddenAttributes` defaults:** ship a non-empty default (`email`, `phone`) knowing the
   dashboard shows these anyway, or default empty for parity with the REST API?
4. **Tool naming:** kebab-case derived names (`list-segments`) are the SDK default; confirm
   no client in scope needs `snake_case` overrides via `#[Name]`.
