<?php

use Dclaysmith\LaravelCascade\Actions\ExportSegment;
use Dclaysmith\LaravelCascade\Contracts\Segment;
use Mockery;
use Workbench\App\Models\User;

test('exports segment', function () {

    $segmentDefinitionMock = Mockery::mock(Segment::class);
    $segmentDefinitionMock->shouldReceive('toBuilder')->andReturn(User::query());

    (new ExportSegment)(1, $segmentDefinitionMock);
});
