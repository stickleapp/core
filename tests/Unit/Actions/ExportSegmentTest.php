<?php

use Mockery;
use StickleApp\Core\Actions\ExportSegment;
use StickleApp\Core\Contracts\Segment;
use Workbench\App\Models\User;

test('exports segment', function () {

    $segmentDefinitionMock = Mockery::mock(Segment::class);
    $segmentDefinitionMock->shouldReceive('toBuilder')->andReturn(User::query());

    (new ExportSegment)(1, $segmentDefinitionMock);
});
