<?php

declare(strict_types=1);

namespace Dclaysmith\LaravelCascade\Enums;

/**
 * @internal
 */
enum RequestType: string
{
    case TRACK = 'track';
    case PAGE = 'page';
    case GROUP = 'group';
    case IDENTIFY = 'identify';
}
