<?php

namespace StickleApp\Core\Enums;

enum DataType: string
{
    case STRING = 'string';
    case INTEGER = 'integer';
    case FLOAT = 'float';
    case BOOLEAN = 'boolean';
    case DATE = 'date';
    case DATETIME = 'datetime';
    case CURRENCY = 'currency';
    case TIME = 'time';
}
