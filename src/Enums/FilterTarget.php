<?php

declare(strict_types=1);

namespace StickleApp\Core\Enums;

/**
 * @internal
 */
enum FilterTarget: string
{
    case EVENT_COUNT = 'Event Count';
    case EVENT_COUNT_DELTA = 'Event Count Delta';
    case PAGE_VIEW_COUNT = 'Page View Count';
    case PAGE_VIEW_COUNT_DELTA = 'Page View Count Delta';
    case MODEL_ATTRIBUTE_DELTA = 'Model Attribute Delta';
    case MODEL_TRAIT = 'Model Trait'; // SENT VIA TRACKING CODE
    case MODEL_TRAIT_DELTA = 'Model Trait Delta';
    case TAG = 'Tag';
    case SEGMENT = 'Segment';
    case PLAYBOOK = 'Playbook';
    // More filter types can be added here
}
