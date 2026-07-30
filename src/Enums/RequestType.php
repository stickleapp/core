<?php

declare(strict_types=1);

namespace StickleApp\Core\Enums;

/**
 * @internal
 */
enum RequestType: string
{
    case TRACK = 'track';
    case PAGE = 'page';

    /**
     * There is deliberately no `group` or `identify` case. Those verbs exist
     * in analytics clients like Segment because the client has no knowledge
     * of the host application's domain. Stickle does: identity comes from
     * the session (IngestController falls back to $request->user()), and
     * group membership comes from a declared Eloquent relationship between
     * the host app's models. Adding browser-supplied verbs for either would
     * create a second, unauthenticated source of truth for facts the
     * database already holds. See GitHub issue #38.
     */
}
