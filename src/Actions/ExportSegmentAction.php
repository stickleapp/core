<?php

declare(strict_types=1);

namespace StickleApp\Core\Actions;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use StickleApp\Core\Contracts\SegmentContract;

class ExportSegmentAction
{
    public function __invoke(
        int $segmentId,
        SegmentContract $segmentDefinition
    ): string {
        Log::info('ExportSegmentAction', [$segmentId]);

        $builder = $segmentDefinition->toBuilder();

        $builder
            ->selectRaw($builder->getModel()->getTable().'.'.$builder->getModel()->getKeyName().' as object_uid')
            ->selectRaw("{$segmentId} as segment_id");

        $csvFile = tmpfile();

        $csvPath = stream_get_meta_data($csvFile)['uri'];

        if (! $fd = fopen($csvPath, 'w')) {
            throw new \Exception('Cannot open file');
        }

        $builder->cursor()->each(function ($item) use ($fd) {
            /** @var array<int, string> $asArray * */
            $asArray = $item->toArray();
            fputcsv($fd, $asArray);
        });

        fclose($fd);

        $filename = $this->formatFilename($segmentId);

        /** @var string $disk * */
        $disk = config('stickle.filesystem.disk');
        Storage::disk($disk)->putFileAs('', $csvPath, $filename);

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
