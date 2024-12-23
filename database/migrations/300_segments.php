<?php

use Illuminate\Container\Attributes\Config;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function __construct(
        #[Config('cascade.database.tablePrefix')] protected ?string $prefix = null,
    ) {
        $this->prefix = config('cascade.database.tablePrefix');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $prefix = $this->prefix;

        Schema::create("{$prefix}object_segment", function (Blueprint $table) {
            $table->id();
            $table->text('object_uid')->nullable(false);
            $table->unsignedBigInteger('segment_id')->nullable(false);
            $table->timestamps();

            $table->unique(['object_uid', 'segment_id']);
        });

        Schema::create("{$prefix}object_segment_audit", function (Blueprint $table) {
            $table->id();
            $table->text('object_uid')->nullable(false);
            $table->unsignedBigInteger('segment_id')->nullable(false);
            $table->text('operation')->nullable(false); // enum
            $table->timestamp('recorded_at')->nullable(false);
        });

        Schema::create("{$prefix}object_segment_statistics", function (Blueprint $table) {
            $table->id();
            $table->text('object_uid')->nullable(false);
            $table->unsignedBigInteger('segment_id')->nullable(false);
            $table->timestamp('first_enter_recorded_at')->nullable(true);
            $table->timestamp('first_exit_recorded_at')->nullable(true);
            $table->timestamp('last_enter_recorded_at')->nullable(true);
            $table->timestamp('last_exit_recorded_at')->nullable(true);

            $table->unique(['object_uid', 'segment_id']);
        });

        Schema::create("{$prefix}segment_groups", function (Blueprint $table) {
            $table->id();
            $table->text('name');
            $table->timestamps();
        });

        Schema::create("{$prefix}segments", function (Blueprint $table) use ($prefix) {
            $table->id();
            $table->unsignedBigInteger('segment_group_id')->nullable(true);
            $table->text('model')->nullable(false);
            $table->text('as_class')->nullable(true);
            $table->jsonb('as_json')->nullable(true);
            $table->integer('sort_order')->nullable(false)->default(0);
            $table->integer('export_interval')->nullable(false)->default(360);
            $table->timestamp('last_exported_at')->nullable(true);
            $table->timestamps();

            $table->unique('model', 'as_class', 'as_json');
            $table->foreign('segment_group_id')->references('id')->on("{$prefix}segment_groups");
            $table->index('segment_group_id');
        });

        Schema::create("{$prefix}segment_exports", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('segment_id')->nullable(false);
            $table->text('result')->nullable(false);
            $table->text('error')->nullable(true);
            $table->timestamps();
        });

        $sql = <<<'eof'
CREATE OR REPLACE FUNCTION process_object_segment_audit() RETURNS TRIGGER AS $process_object_segment_audit$
    BEGIN
        --
        -- Create a row in process_object_segment_audit to reflect the operation performed on object_segment_audit,
        -- make use of the special variable TG_OP to work out the operation.
        --
        IF (TG_OP = 'DELETE') THEN
            INSERT INTO         %sobject_segment_audit 
                                (object_uid, 
                                segment_id, 
                                operation, 
                                recorded_at) 
            VALUES              (OLD.object_uid, 
                                OLD.segment_id, 
                                'EXIT', 
                                CURRENT_TIMESTAMP);

            INSERT INTO         %sobject_segment_statistics
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
            WHERE               CURRENT_TIMESTAMP < %sobject_segment_statistics.first_exit_recorded_at 
                                OR %sobject_segment_statistics.first_exit_recorded_at IS NULL;

            INSERT INTO         %sobject_segment_statistics
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
            WHERE               CURRENT_TIMESTAMP > %sobject_segment_statistics.last_exit_recorded_at 
                                OR %sobject_segment_statistics.last_exit_recorded_at IS NULL;
        ELSIF (TG_OP = 'INSERT') THEN
            INSERT INTO         %sobject_segment_audit 
                                (object_uid, 
                                segment_id, 
                                operation, 
                                recorded_at) 
            VALUES              (NEW.object_uid, 
                                NEW.segment_id, 
                                'ENTER', 
                                NEW.created_at);

            INSERT INTO         %sobject_segment_statistics
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
            WHERE               NEW.created_at < %sobject_segment_statistics.first_enter_recorded_at
                                OR %sobject_segment_statistics.first_enter_recorded_at IS NULL;

            INSERT INTO         %sobject_segment_statistics
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
            WHERE               NEW.created_at > %sobject_segment_statistics.last_enter_recorded_at
                                OR %sobject_segment_statistics.last_enter_recorded_at IS NULL;
        END IF;
        RETURN NEW;
    END;
$process_object_segment_audit$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS object_segment_on_insert_delete ON object_segment;        
CREATE TRIGGER object_segment_on_insert_delete
AFTER INSERT OR DELETE ON %sobject_segment
    FOR EACH ROW EXECUTE PROCEDURE process_object_segment_audit();

DROP FUNCTION IF EXISTS f_deactivate_object_segments(segmentId INT, tempTable VARCHAR);
CREATE FUNCTION f_deactivate_object_segments(segmentId INT, tempTable VARCHAR) RETURNS void AS $$
BEGIN
    EXECUTE format('
WITH now_inactive AS (
    SELECT              %sobject_segment.object_uid,
                        %sobject_segment.segment_id
    FROM                %sobject_segment
    LEFT JOIN           %I temp
    ON                  temp.object_uid = %sobject_segment.object_uid
    AND                 %sobject_segment.segment_id = %L
    WHERE               temp.object_uid IS NULL
    AND                 %sobject_segment.segment_id = %L
)
DELETE FROM             %sobject_segment
USING                   now_inactive
WHERE                   %sobject_segment.object_uid = now_inactive.object_uid
AND                     %sobject_segment.segment_id = now_inactive.segment_id;', tempTable, segmentId, segmentId);
END; $$
LANGUAGE PLPGSQL;

-- -----------------------------------------------------------------------------
-- This function will enter new records into object_segment IF and ONLY IF they
-- are not actually in the table already. This will significantly reduce updates
-- on the object_segment table and reduce table bloat and the need to autovacuum
-- -----------------------------------------------------------------------------
DROP FUNCTION IF EXISTS f_activate_object_segments(segmentId INT, tempTable VARCHAR);
CREATE FUNCTION f_activate_object_segments(segmentId INT, tempTable VARCHAR) RETURNS void AS $$
BEGIN
    EXECUTE format('
INSERT INTO             %sobject_segment
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
LEFT JOIN               %sobject_segment
ON                      %sobject_segment.object_uid = %I.object_uid
AND                     %sobject_segment.segment_id = %ssegments.id
WHERE                   %sobject_segment.object_uid IS NULL 
ON CONFLICT             DO NOTHING;', tempTable, tempTable, segmentId, tempTable);
END; $$
LANGUAGE PLPGSQL;
eof;
        \DB::connection()->getPdo()->exec(preg_replace('/%s/', $prefix, $sql));
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $prefix = $this->prefix;

        $sql = <<<'eof'
DROP FUNCTION IF EXISTS f_activate_object_segments(segmentId INT, tempTable VARCHAR);
DROP FUNCTION IF EXISTS f_deactivate_object_segments(segmentId INT, tempTable VARCHAR);
eof;
        \DB::connection()->getPdo()->exec($sql);

        Schema::dropIfExists("{$prefix}segment_exports");
        Schema::dropIfExists("{$prefix}segments");
        Schema::dropIfExists("{$prefix}segment_groups");
        Schema::dropIfExists("{$prefix}object_segment_statistics");
        Schema::dropIfExists("{$prefix}object_segment_audit");
        Schema::dropIfExists("{$prefix}object_segment");
    }
};
