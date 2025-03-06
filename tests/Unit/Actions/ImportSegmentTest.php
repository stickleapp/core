<?php

use Illuminate\Support\Facades\Storage;
use StickleApp\Core\Actions\ImportSegment;

beforeEach(function () {
    Storage::fake('local');
});

it('imports segment data from CSV', function () {

    // Create a mock CSV file
    $csvContent = "user1,1\nuser2,1\nuser3,1";
    $exportFilename = 'segment-1-2025-03-05-120000.csv';
    Storage::disk('local')->put($exportFilename, $csvContent);

    // Create a partial mock of ImportSegment
    $importSegment = Mockery::mock(ImportSegment::class)->makePartial();

    // Mock the methods that would interact with the database
    $importSegment->shouldReceive('createTmpTable')->once()->andReturn(null);
    $importSegment->shouldReceive('loadTmpTable')->once()->andReturn(null);
    $importSegment->shouldReceive('executeQuery')->once()->andReturn(null);

    // Since we're mocking the file system operations too
    $importSegment->shouldReceive('writeLocalFile')->once()->andReturn(null);

    // We'll let localFilename and tempTableName run normally

    // Execute the action
    $importSegment->__invoke(1, $exportFilename);

    // Assertions are handled through Mockery expectations
});

it('throws exception when file is missing', function () {
    $importSegment = new ImportSegment;
    $nonExistentFile = 'segment-1-does-not-exist.csv';

    expect(fn () => $importSegment(1, $nonExistentFile))
        ->toThrow(Exception::class, 'File missing');
});

it('formats temp table name correctly', function () {
    $importSegment = new ImportSegment;
    $filename = 'segment-1-2025-03-05-120000.csv';

    $result = $importSegment->tempTableName($filename);

    expect($result)->toBe('_segment_1_2025_03_05_120000');
});

it('formats local filename correctly', function () {
    $importSegment = new ImportSegment;
    $exportFilename = 'segment-1-2025-03-05-120000.csv';

    $result = $importSegment->localFilename($exportFilename);

    // Should start with /tmp/ followed by a UUID and the original filename
    expect($result)->toStartWith('/tmp/')
        ->toContain($exportFilename)
        ->toMatch('/^\/tmp\/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}-segment-1-2025-03-05-120000\.csv$/');
});

afterEach(function () {
    Mockery::close();
});
