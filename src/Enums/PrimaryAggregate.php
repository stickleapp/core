<?php

namespace StickleApp\Core\Enums;

enum PrimaryAggregate: string
{
    case SUM = 'sum';
    case AVG = 'avg';
    case MIN = 'min';
    case MAX = 'max';
    case COUNT = 'count';
    case MEDIAN = 'median';
}
