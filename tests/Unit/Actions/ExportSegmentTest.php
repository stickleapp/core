<?php

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use StickleApp\Core\Actions\ExportSegmentAction;
use StickleApp\Core\Contracts\SegmentContract;

beforeEach(function () {
    Storage::fake('local');
});

it('exports segment data to a CSV file', function () {

    // Create a mock segment definition
    $segmentDefinition = Mockery::mock(SegmentContract::class);

    // Mock the model
    $mockModel = Mockery::mock(Model::class);
    $mockModel->shouldReceive('getTable')->andReturn('users');
    $mockModel->shouldReceive('getKeyName')->andReturn('id');

    // Mock the builder and its methods
    $mockBuilder = Mockery::mock(Builder::class);
    $mockBuilder->shouldReceive('getModel')->andReturn($mockModel);
    $mockBuilder->shouldReceive('selectRaw')->with('users.id as object_uid')->andReturnSelf();
    $mockBuilder->shouldReceive('selectRaw')->with('1 as segment_id')->andReturnSelf();

    // Create mock data for the cursor
    $mockItem1 = Mockery::mock();
    $mockItem1->shouldReceive('toArray')->andReturn(['object_uid' => '1', 'segment_id' => '1']);

    $mockItem2 = Mockery::mock();
    $mockItem2->shouldReceive('toArray')->andReturn(['object_uid' => '2', 'segment_id' => '1']);

    // Create a collection with the mock items
    $mockCollection = collect([$mockItem1, $mockItem2]);

    // Mock the cursor method to return the collection
    $mockBuilder->shouldReceive('cursor')->andReturn($mockCollection);

    // Set up the segment definition to return the mock builder
    $segmentDefinition->shouldReceive('toBuilder')->andReturn($mockBuilder);

    // Execute the action
    $action = new ExportSegmentAction;
    $filename = $action(1, $segmentDefinition);

    // Assert the format of the filename
    expect($filename)->toStartWith('segment-1-')->toEndWith('.csv');

    // Assert the file was stored
    Storage::disk('local')->assertExists($filename);

    // Clean up the mock
    Mockery::close();
});

it('formats filename correctly', function () {

    $action = new ExportSegmentAction;

    $filename = $action->formatFilename(99);

    // Get today's date in the format Y-m-d-H-i-s
    $datePattern = date('Y-m-d-H-i-s');

    expect($filename)->toBe("segment-99-{$datePattern}.csv");
});

it('handles empty segments', function () {

    // Create a mock segment definition
    $segmentDefinition = Mockery::mock(SegmentContract::class);

    // Mock the model
    $mockModel = Mockery::mock(Model::class);
    $mockModel->shouldReceive('getTable')->andReturn('users');
    $mockModel->shouldReceive('getKeyName')->andReturn('id');

    // Mock the builder and its methods
    $mockBuilder = Mockery::mock(Builder::class);
    $mockBuilder->shouldReceive('getModel')->andReturn($mockModel);
    $mockBuilder->shouldReceive('selectRaw')->with('users.id as object_uid')->andReturnSelf();
    $mockBuilder->shouldReceive('selectRaw')->with('1 as segment_id')->andReturnSelf();

    // Empty collection for an empty segment
    $mockBuilder->shouldReceive('cursor')->andReturn(collect([]));

    // Set up the segment definition to return the mock builder
    $segmentDefinition->shouldReceive('toBuilder')->andReturn($mockBuilder);

    // Execute the action
    $action = new ExportSegmentAction;
    $filename = $action(1, $segmentDefinition);

    // Assert the file was stored even though the segment is empty
    Storage::disk('local')->assertExists($filename);

    // The file should exist but be empty or only contain basic CSV structure
    $fileContents = Storage::disk('local')->get($filename);
    expect($fileContents)->toBeString();

    // Clean up the mock
    Mockery::close();
});
