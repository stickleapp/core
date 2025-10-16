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

        Log::info(self::class, func_get_args());

        $builder = $segmentDefinition->toBuilder();

        $builder
            ->selectRaw($builder->getModel()->getTable().'.'.$builder->getModel()->getKeyName().' as object_uid')
            ->selectRaw("{$segmentId} as segment_id");

        $csvFile = tmpfile();

        $metaData = stream_get_meta_data($csvFile);
        if (! isset($metaData['uri'])) {
            throw new \Exception('Cannot get temporary file path');
        }

        /** @var string $csvPath */
        $csvPath = $metaData['uri'];

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
        $disk = config('stickle.filesystem.disks.exports');
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
