<?php

declare(strict_types=1);

namespace StickleApp\Core\Views\Components\Concerns;

/**
 * Shared polling settings for the components that stream live updates.
 *
 * Reverb speaks only the Pusher WebSocket protocol, so pusher-js cannot fall
 * back to its own HTTP transports. The components poll the requests endpoint
 * instead whenever the socket is unavailable.
 *
 * The package config is published rather than merged, so the injected values
 * are absent until an installing application publishes it. The constants below
 * are what an unpublished install falls back to.
 */
trait ResolvesPolling
{
    /**
     * Seconds between polls while the websocket is unavailable.
     */
    public const DEFAULT_POLL_INTERVAL = 15;

    /**
     * Seconds between polls, resolved from the attribute, then config.
     */
    public int $pollInterval;

    /**
     * Whether to poll at all when the websocket is unavailable.
     */
    public bool $pollingEnabled;

    /**
     * Environment variables arrive as strings, so both values are coerced.
     */
    protected function resolvePolling(int|string|null $pollInterval, bool|string|int|null $pollingEnabled): void
    {
        $interval = (int) ($pollInterval ?? self::DEFAULT_POLL_INTERVAL);

        $this->pollInterval = $interval > 0 ? $interval : self::DEFAULT_POLL_INTERVAL;

        $this->pollingEnabled = filter_var(
            $pollingEnabled ?? true,
            FILTER_VALIDATE_BOOL
        );
    }
}
