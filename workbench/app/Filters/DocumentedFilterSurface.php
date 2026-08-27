<?php

declare(strict_types=1);

namespace Workbench\App\Filters;

use StickleApp\Core\Filters\Base as Filter;

/**
 * Not runtime code. This exists so the package's own PHPStan run exercises the
 * filter surface a consumer actually writes against.
 *
 * The surface is entirely magic -- __callStatic() for the target, __call() for
 * the test -- so nothing but static analysis can catch a wrong or missing
 * method annotation on Filters\Base. A docblock has no runtime behaviour to
 * assert on, which is why this is a fixture and not a test.
 *
 * Each of the ten directly-callable targets appears once, paired with a test,
 * because dropping the static annotations alone would not have been caught:
 * an unresolved static call poisons the type and PHPStan then stops checking
 * the rest of the chain.
 */
class DocumentedFilterSurface
{
    /** @return array<int, Filter> */
    public static function all(): array
    {
        return [
            Filter::boolean('is_active')->isTrue(),
            Filter::date('signed_at')->occurredAfter('2026-01-01'),
            Filter::datetime('seen_at')->isBefore('2026-01-01'),
            Filter::number('order_count')->greaterThan(1),
            Filter::text('user_type')->equals('owner'),
            Filter::segment('ActiveUsers')->isInSegment(),
            Filter::segmentHistory('VipUsers')->hasBeenInSegment(),
            Filter::eventCount(event: 'clicked:something')->count()->greaterThan(2),
            Filter::requestCount()->count()->greaterThan(3),
            Filter::sessionCount()->count()->greaterThan(4),
        ];
    }
}
