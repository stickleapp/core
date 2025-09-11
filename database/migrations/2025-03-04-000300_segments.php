<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // $prefix = Config::string('stickle.database.tablePrefix');
        $prefix = config('stickle.database.tablePrefix');

        Schema::create("{$prefix}segment_groups", function (Blueprint $table) {
            $table->id();
            $table->text('name');
            $table->timestamps();
        });

        Schema::create("{$prefix}segments", function (Blueprint $table) use ($prefix) {
            $table->id();
            $table->unsignedBigInteger('segment_group_id')->nullable(true);
            $table->text('name')->nullable(false);
            $table->text('description')->nullable(true);
            $table->text('model_class')->nullable(false);
            $table->text('as_class')->nullable(true);
            $table->jsonb('as_json')->nullable(true);
            $table->integer('sort_order')->nullable(false)->default(0);
            $table->integer('export_interval')->nullable(false)->default(360);
            $table->timestamp('last_exported_at')->nullable(true);
            $table->timestamps();

            $table->unique(['model_class', 'as_class']);
            $table->foreign('segment_group_id')->references('id')->on("{$prefix}segment_groups");
            $table->index('segment_group_id');
        });

        Schema::create("{$prefix}model_segment", function (Blueprint $table) {
            $table->id();
            $table->text('object_uid')->nullable(false);
            $table->unsignedBigInteger('segment_id')->nullable(false);
            $table->timestamps();

            $table->unique(['object_uid', 'segment_id']);

            $table->index('object_uid');
            $table->index('segment_id');
        });

        Schema::create("{$prefix}model_segment_audit", function (Blueprint $table) {
            $table->id();
            $table->text('object_uid')->nullable(false);
            $table->unsignedBigInteger('segment_id')->nullable(false);
            $table->text('operation')->nullable(false); // enum
            $table->timestamp('recorded_at')->nullable(false);
            $table->timestamp('event_processed_at')->nullable(true);

            $table->index('object_uid');
            $table->index('segment_id');
        });

        Schema::create("{$prefix}model_segment_statistics", function (Blueprint $table) {
            $table->id();
            $table->text('object_uid')->nullable(false);
            $table->unsignedBigInteger('segment_id')->nullable(false);
            $table->timestamp('first_enter_recorded_at')->nullable(true);
            $table->timestamp('first_exit_recorded_at')->nullable(true);
            $table->timestamp('last_enter_recorded_at')->nullable(true);
            $table->timestamp('last_exit_recorded_at')->nullable(true);
            $table->timestamps();

            $table->unique(['object_uid', 'segment_id']);

            $table->index('object_uid');
            $table->index('segment_id');
        });

        $sql = <<<'eof'
CREATE OR REPLACE FUNCTION process_model_segment_audit() RETURNS TRIGGER AS $process_model_segment_audit$
    BEGIN
        --
        -- Create a row in process_model_segment_audit to reflect the operation performed on model_segment_audit,
        -- make use of the special variable TG_OP to work out the operation.
        --
        IF (TG_OP = 'DELETE') THEN
            INSERT INTO         %smodel_segment_audit 
                                (object_uid, 
                                segment_id, 
                                operation, 
                                recorded_at) 
            VALUES              (OLD.object_uid, 
                                OLD.segment_id, 
                                'EXIT', 
                                CURRENT_TIMESTAMP);

            INSERT INTO         %smodel_segment_statistics
                                (object_uid, 
                                segment_id, 
                                first_exit_recorded_at) 
            VALUES              (OLD.object_uid, 
                                OLD.segment_id, 
                                CURRENT_TIMESTAMP)
            ON CONFLICT         (object_uid, 
                                segment_id)
            DO UPDATE           
            SET                 first_exit_recorded_at = CURRENT_TIMESTAMP
            WHERE               CURRENT_TIMESTAMP < %smodel_segment_statistics.first_exit_recorded_at 
                                OR %smodel_segment_statistics.first_exit_recorded_at IS NULL;

            INSERT INTO         %smodel_segment_statistics
                                (object_uid, 
                                segment_id, 
                                last_exit_recorded_at) 
            VALUES              (OLD.object_uid, 
                                OLD.segment_id, 
                                CURRENT_TIMESTAMP)
            ON CONFLICT         (object_uid, 
                                segment_id)
            DO UPDATE           
            SET                 last_exit_recorded_at = CURRENT_TIMESTAMP
            WHERE               CURRENT_TIMESTAMP > %smodel_segment_statistics.last_exit_recorded_at 
                                OR %smodel_segment_statistics.last_exit_recorded_at IS NULL;
        ELSIF (TG_OP = 'INSERT') THEN
            INSERT INTO         %smodel_segment_audit 
                                (object_uid, 
                                segment_id, 
                                operation, 
                                recorded_at) 
            VALUES              (NEW.object_uid, 
                                NEW.segment_id, 
                                'ENTER', 
                                NEW.created_at);

            INSERT INTO         %smodel_segment_statistics
                                (object_uid, 
                                segment_id, 
                                first_enter_recorded_at) 
            VALUES              (NEW.object_uid, 
                                NEW.segment_id, 
                                NEW.created_at)
            ON CONFLICT         (object_uid, 
                                segment_id)
            DO UPDATE           
            SET                 first_enter_recorded_at = NEW.created_at
            WHERE               NEW.created_at < %smodel_segment_statistics.first_enter_recorded_at
                                OR %smodel_segment_statistics.first_enter_recorded_at IS NULL;

            INSERT INTO         %smodel_segment_statistics
                                (object_uid, 
                                segment_id, 
                                last_enter_recorded_at) 
            VALUES              (NEW.object_uid, 
                                NEW.segment_id, 
                                NEW.created_at)
            ON CONFLICT         (object_uid, 
                                segment_id)
            DO UPDATE           
            SET                 last_enter_recorded_at = NEW.created_at
            WHERE               NEW.created_at > %smodel_segment_statistics.last_enter_recorded_at
                                OR %smodel_segment_statistics.last_enter_recorded_at IS NULL;
        END IF;
        RETURN NEW;
    END;
$process_model_segment_audit$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS model_segment_on_insert_delete ON model_segment;        
CREATE TRIGGER model_segment_on_insert_delete
AFTER INSERT OR DELETE ON %smodel_segment
    FOR EACH ROW EXECUTE PROCEDURE process_model_segment_audit();

DROP FUNCTION IF EXISTS f_deactivate_model_segments(segmentId INT, tempTable VARCHAR);
CREATE FUNCTION f_deactivate_model_segments(segmentId INT, tempTable VARCHAR) RETURNS void AS $$
BEGIN
    EXECUTE format('
WITH now_inactive AS (
    SELECT              %smodel_segment.object_uid,
                        %smodel_segment.segment_id
    FROM                %smodel_segment
    LEFT JOIN           %I temp
    ON                  temp.object_uid = %smodel_segment.object_uid
    AND                 %smodel_segment.segment_id = %L
    WHERE               temp.object_uid IS NULL
    AND                 %smodel_segment.segment_id = %L
)
DELETE FROM             %smodel_segment
USING                   now_inactive
WHERE                   %smodel_segment.object_uid = now_inactive.object_uid
AND                     %smodel_segment.segment_id = now_inactive.segment_id;', tempTable, segmentId, segmentId);
END; $$
LANGUAGE PLPGSQL;

-- -----------------------------------------------------------------------------
-- This function will enter new records into model_segment IF and ONLY IF they
-- are not actually in the table already. This will significantly reduce updates
-- on the model_segment table and reduce table bloat and the need to autovacuum
-- -----------------------------------------------------------------------------
DROP FUNCTION IF EXISTS f_activate_model_segments(segmentId INT, tempTable VARCHAR);
CREATE FUNCTION f_activate_model_segments(segmentId INT, tempTable VARCHAR) RETURNS void AS $$
BEGIN
    EXECUTE format('
INSERT INTO             %smodel_segment
                        (
                            object_uid, 
                            segment_id,
                            created_at,
                            updated_at
                        )
SELECT                  %I.object_uid,
                        %ssegments.id AS segment_id,
                        CURRENT_TIMESTAMP,
                        CURRENT_TIMESTAMP
FROM                    %I -- temp table
JOIN                    %ssegments
ON                      %ssegments.id = %L
LEFT JOIN               %smodel_segment
ON                      %smodel_segment.object_uid = %I.object_uid
AND                     %smodel_segment.segment_id = %ssegments.id
WHERE                   %smodel_segment.object_uid IS NULL 
ON CONFLICT             DO NOTHING;', tempTable, tempTable, segmentId, tempTable);
END; $$
LANGUAGE PLPGSQL;
eof;

        if ($sql = preg_replace('/%s/', $prefix, $sql)) {
            DB::connection()->getPdo()->exec($sql);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // $prefix = Config::string('stickle.database.tablePrefix');
        $prefix = config('stickle.database.tablePrefix');

        $sql = <<<'eof'
DROP FUNCTION IF EXISTS f_activate_model_segments(segmentId INT, tempTable VARCHAR);
DROP FUNCTION IF EXISTS f_deactivate_model_segments(segmentId INT, tempTable VARCHAR);
eof;
        DB::connection()->getPdo()->exec($sql);

        Schema::dropIfExists("{$prefix}model_segment_statistics");
        Schema::dropIfExists("{$prefix}model_segment_audit");
        Schema::dropIfExists("{$prefix}model_segment");
        Schema::dropIfExists("{$prefix}segments");
        Schema::dropIfExists("{$prefix}segment_groups");
    }
};
