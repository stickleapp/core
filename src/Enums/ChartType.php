<?php

namespace StickleApp\Core\Enums;

enum ChartType: string
{
    case LINE = 'line';
    case BAR = 'bar';
    case PIE = 'pie';
    case DOUGHNUT = 'doughnut';
    case AREA = 'area';
    case RADAR = 'radar';
    case SCATTER = 'scatter';
}
