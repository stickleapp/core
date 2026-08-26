<?php

declare(strict_types=1);

namespace StickleApp\Core\Repositories;

use DateTimeInterface;
use Illuminate\Container\Attributes\Config;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use StickleApp\Core\Contracts\AnalyticsRepositoryContract;

/**
 * @internal
 */
final readonly class PostgresAnalyticsRepository implements AnalyticsRepositoryContract
{
    /**
     * Creates a new analytics repository instance.
     */
    public function __construct(
        #[Config('stickle.database.tablePrefix')]
        private ?string $prefix = null,
    ) {}

    /**
     * The grains the migration defines a {prefix}rollup_requests_* function for.
     * The grain is interpolated into the function name, so it is whitelisted
     * here rather than escaped -- a value from outside this list is a bug, not
     * user input to sanitise.
     *
     * @var list<string>
     */
    private const array GRAINS = ['1min', '5min', '1hr', '1day'];

    public function rollupRequests(string $grain): void
    {
        throw_unless(
            in_array($grain, self::GRAINS, true),
            InvalidArgumentException::class,
            sprintf('Unknown rollup grain [%s]. Expected one of: %s.', $grain, implode(', ', self::GRAINS))
        );

        DB::statement(sprintf('SELECT %srollup_requests_%s();', $this->prefix, $grain));
    }

    public function rollupSessions(DateTimeInterface $startDate): void
    {
        $sql = <<<sql
INSERT INTO {$this->prefix}sessions_rollup_1day (
    model_class, 
    object_uid, 
    day, 
    session_count
)
    WITH first_session_events AS (
        SELECT
            model_class,
            object_uid,
            session_uid,
            MIN(DATE(timestamp)) AS first_day
        FROM
            {$this->prefix}requests 
        WHERE 
            offline = FALSE AND timestamp > '%s'
        GROUP BY
            model_class,
            object_uid,
            session_uid
    )
    SELECT
        model_class,
        object_uid,
        first_day AS day,
        COUNT(DISTINCT session_uid) AS session_count
    FROM
        first_session_events
    GROUP BY
        model_class,
        object_uid,
        first_day
ON CONFLICT (model_class, object_uid, day) DO UPDATE SET session_count = EXCLUDED.session_count;
sql;

        DB::statement(sprintf($sql, $startDate->format('Y-m-d'), $startDate->format('Y-m-d')));
    }
}
