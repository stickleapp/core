<?php

declare(strict_types=1);

namespace Dclaysmith\LaravelCascade\Enums;

/**
 * @internal
 */
enum FilterTest: string
{
    case BOOLEAN_FALSE = 'False';
    case BO0LEAN_NOT_FALSE = 'Not False';
    case BOOLEAN_NOT_TRUE = 'Not True';
    case BOOLEAN_TRUE = 'True';
    case DATE_AFTER = 'After';
    case DATE_BEFORE = 'Before';
    case DATE_AFTER_FUTURE = 'After Future';
    case DATE_AFTER_PAST = 'After Past';
    // More here
}
