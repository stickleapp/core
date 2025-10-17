<?php

declare(strict_types=1);

namespace StickleApp\Core\Actions;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImportSegmentAction
{
    public function __invoke(
        int $segmentId,
        string $exportFilename
    ): void {

        Log::info(self::class, func_get_args());

        // Retrieve from storage
        /** @var string $disk * */
        $disk = config('stickle.filesystem.disks.exports');
        $filesystem = Storage::disk($disk);

        throw_if($filesystem->missing($exportFilename), Exception::class, 'File missing');

        $contents = $filesystem->get($exportFilename) ?? '';

        $localFilename = $this->localFilename($exportFilename);

        $tempTableName = $this->tempTableName($exportFilename);

        $this->writeLocalFile($localFilename, $contents);

        $this->createTmpTable($tempTableName);

        $this->loadTmpTable($tempTableName, $localFilename);

        $this->executeQuery($segmentId, $tempTableName);

        if (file_exists($localFilename)) {
            unlink($localFilename);
        }
    }

    public function executeQuery(int $segmentId, string $tempTableName): void
    {
        $sql = <<<EOF
    -- +----------------------------------------------------------------------------
    -- + "DEACTIVATE" STATUSES
    -- +----------------------------------------------------------------------------
    SELECT f_deactivate_model_segments({$segmentId}, '{$tempTableName}');
    
    -- +----------------------------------------------------------------------------
    -- + UPSERT RECORDS
    -- +----------------------------------------------------------------------------
    SELECT f_activate_model_segments({$segmentId}, '{$tempTableName}');
    
    -- +----------------------------------------------------------------------------
    -- + DELETE TEMP TABLE
    -- +----------------------------------------------------------------------------
    DROP TABLE IF EXISTS {$tempTableName};
    EOF;

        DB::connection()
            ->getPdo()
            ->exec($this->wrapInTransaction($sql));
    }

    public function writeLocalFile(string $localFilename, string $contents): void
    {
        $handle = fopen($localFilename, 'w');

        if ($handle) {
            fwrite($handle, $contents);
            fclose($handle);
        }
    }

    public function createTmpTable(string $tempTableName): void
    {
        $sql = <<<eof
DROP TABLE IF EXISTS {$tempTableName};
CREATE UNLOGGED TABLE {$tempTableName} (
    object_uid text,
    segment_id bigint
);
eof;
        DB::connection()
            ->getPdo()
            ->exec($sql);
    }

    public function loadTmpTable(string $tempTableName, string $tempFilename): void
    {
        $sql =
            "\copy ".
            $tempTableName.
            " (object_uid, segment_id) FROM '".
            $tempFilename.
            "' CSV;";

        $cmd =
            'PGPASSWORD='.
            DB::connection()->getConfig('password').
            ' psql '.
            ' -h '.
            DB::connection()->getConfig('host').
            ' -U '.
            DB::connection()->getConfig('username').
            ' -d '.
            DB::connection()->getConfig('database').
            ' -c "'.
            $sql.
            '"';

        exec($cmd, $output, $res);
    }

    public function tempTableName(string $filename): string
    {
        $tableName = str_replace(
            '.csv',
            '',
            str_replace('-', '_', $filename)
        );

        return '_'.$tableName;
    }

    public function localFilename(string $exportFilename): string
    {
        return '/tmp/'.
            Str::uuid().
            '-'.
            $exportFilename;
    }

    public function wrapInTransaction(string $sql): string
    {
        return implode(' ', ['BEGIN;', $sql, 'COMMIT;']);
    }
}
