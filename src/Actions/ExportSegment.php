<?php

declare(strict_types=1);

namespace Dclaysmith\LaravelCascade\Actions;

use Dclaysmith\LaravelCascade\Contracts\Segment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ExportSegment
{
    public function __invoke(
        int $segmentId,
        Segment $segmentDefinition
    ): string {
        Log::info('Export Segment', [$segmentId]);

        $builder = $segmentDefinition->toBuilder();

        $builder->selectRaw($builder->getModel()->getTable().'.'.$builder->getModel()->getKeyName().' as object_uid')
            ->selectRaw("{$segmentId} as segment_id");

        $csvFile = tmpfile();
        $csvPath = stream_get_meta_data($csvFile)['uri'];

        $fd = fopen($csvPath, 'w');

        $builder->cursor()->each(function ($item) use ($fd) {
            fputcsv($fd, $item->toArray());
        });

        fclose($fd);

        $filename = $this->formatFilename($segmentId);

        Storage::disk(config('cascade.filesystem.disk'))->putFileAs('', $csvPath, $filename);

        return $filename;
    }

    public function formatFilename(int $segmentId): string
    {
        return 'segment-'.
            $segmentId.
            '-'.
            date('Y-m-d-H-i-s', time()).
            '.csv';
    }
}
