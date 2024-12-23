<?php

declare(strict_types=1);

namespace Dclaysmith\LaravelCascade\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImportSegment
{
    public function __invoke(
        int $segmentId,
        string $exportFilename
    ) {

        // Retrieve from storage
        $storage = Storage::disk(config('cascade.filesystem.disk'));

        if ($storage->missing($exportFilename)) {
            throw new \Exception('File missing');
        }

        $contents = $storage->get($exportFilename);

        $localFilename = $this->localFilename($exportFilename);

        $tempTableName = $this->tempTableName($exportFilename);

        $this->writeLocalFile($localFilename, $contents);

        $this->createTmpTable($tempTableName);

        $this->loadTmpTable($tempTableName, $localFilename);

        $this->executeQuery($segmentId, $tempTableName);

        unlink($localFilename);
    }

    public function executeQuery($segmentId, $tempTableName)
    {
        $sql = <<<EOF
    -- +----------------------------------------------------------------------------
    -- + "DEACTIVATE" STATUSES
    -- +----------------------------------------------------------------------------
    SELECT f_deactivate_object_segments({$segmentId}, '{$tempTableName}');
    
    -- +----------------------------------------------------------------------------
    -- + UPSERT RECORDS
    -- +----------------------------------------------------------------------------
    SELECT f_activate_object_segments({$segmentId}, '{$tempTableName}');
    
    -- +----------------------------------------------------------------------------
    -- + DELETE TEMP TABLE
    -- +----------------------------------------------------------------------------
    DROP TABLE IF EXISTS {$tempTableName};
    EOF;

        DB::connection()
            ->getPdo()
            ->exec($this->wrapInTransaction($sql));
    }

    public function writeLocalFile($localFilename, $contents): void
    {
        $handle = fopen($localFilename, 'w');
        fwrite($handle, $contents);
        fclose($handle);
    }

    public function createTmpTable($tempTableName): void
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

    public function loadTmpTable($tempTableName, $tempFilename)
    {
        $sql =
            "\copy ".
            $tempTableName.
            " (object_uid, segment_id) FROM '".
            $tempFilename.
            "' CSV;";

        $cmd =
            'PGPASSWORD='.
            config('database.connections.pgsql.password').
            ' psql '.
            ' -h '.
            config('database.connections.pgsql.host').
            ' -U '.
            config('database.connections.pgsql.username').
            ' -d '.
            config('database.connections.pgsql.database').
            ' -c "'.
            $sql.
            '"';

        exec($cmd, $output, $res);
    }

    public function tempTableName($filename)
    {
        $tableName = str_replace(
            '.csv',
            '',
            str_replace('-', '_', $filename)
        );

        return '_'.$tableName;
    }

    public function localFilename($exportFilename)
    {
        return '/tmp/'.
            (string) Str::uuid().
            '-'.
            $exportFilename;
    }

    public function wrapInTransaction($sql)
    {
        return $sql;

        return implode(' ', ['BEGIN;', $sql, 'COMMIT;']);
    }
}
