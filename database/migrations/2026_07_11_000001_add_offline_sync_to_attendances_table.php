<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Adds the columns that support the mobile offline attendance queue.
 *
 * The client captures an attendance event locally (with a fresh GPS fix and the
 * office it was validated against) and syncs it later. To keep the record
 * faithful to *when and where it happened*:
 *
 * - `*_captured_at` is the authoritative moment the button was pressed and
 *   drives the attendance date and stored time. Evaluation (late/on-time) still
 *   follows the current, dynamic working-hour rules at sync time.
 * - `*_synced_at` records when the server actually persisted the event (audit).
 * - `*_client_id` is a client-generated idempotency key so a replayed sync
 *   never double-records.
 * - `*_office_id` links the record to the office used for geofence validation.
 * - `*_office_latitude` / `*_office_longitude` / `*_office_radius` FREEZE the
 *   office geofence configuration used at capture time, so a later admin change
 *   to the office never re-decides a past offline event (Option A snapshot).
 * - `*_auto_time` captures whether the device's automatic date & time was on.
 *
 * All columns are nullable so the existing web (Inertia) and online flows are
 * unaffected.
 *
 * SQL Server notes (production is SQL Server 2008 R2):
 * - A plain UNIQUE index treats NULLs as equal (only one NULL allowed), which
 *   would collide across the many online/web records that leave `*_client_id`
 *   NULL. A filtered unique index (`WHERE ... IS NOT NULL`, supported since SQL
 *   Server 2008) is used there instead.
 * - The two office foreign keys use ON DELETE NO ACTION (the default). SET NULL
 *   on two FKs to the same parent table raises "multiple cascade paths"
 *   (Msg 1785); NO ACTION avoids that and also enforces the desired policy: an
 *   office that has been referenced by attendance can no longer be hard-deleted
 *   (deactivate it instead), while an unreferenced office may still be deleted.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Idempotency keys (unique index added separately, driver-aware).
            $table->string('check_in_client_id')->nullable()->after('work_mode');
            $table->string('check_out_client_id')->nullable()->after('check_in_client_id');

            // Authoritative capture time + audit sync time.
            $table->timestamp('check_in_captured_at')->nullable()->after('check_out_client_id');
            $table->timestamp('check_out_captured_at')->nullable()->after('check_in_captured_at');
            $table->timestamp('check_in_synced_at')->nullable()->after('check_out_captured_at');
            $table->timestamp('check_out_synced_at')->nullable()->after('check_in_synced_at');

            // Office reference (FK, ON DELETE NO ACTION — see class docblock).
            $table->foreignId('check_in_office_id')->nullable()
                ->after('check_out_synced_at')
                ->constrained('attendance_locations');
            $table->foreignId('check_out_office_id')->nullable()
                ->after('check_in_office_id')
                ->constrained('attendance_locations');

            // Frozen office geofence snapshot (mirrors attendance_locations
            // column types: decimal(10,7) coords, unsigned int radius metres).
            // The name is audit-only context; geofence validation never uses it.
            $table->decimal('check_in_office_latitude', 10, 7)->nullable()->after('check_out_office_id');
            $table->decimal('check_in_office_longitude', 10, 7)->nullable()->after('check_in_office_latitude');
            $table->unsignedInteger('check_in_office_radius')->nullable()->after('check_in_office_longitude');
            $table->string('check_in_office_name')->nullable()->after('check_in_office_radius');
            $table->decimal('check_out_office_latitude', 10, 7)->nullable()->after('check_in_office_name');
            $table->decimal('check_out_office_longitude', 10, 7)->nullable()->after('check_out_office_latitude');
            $table->unsignedInteger('check_out_office_radius')->nullable()->after('check_out_office_longitude');
            $table->string('check_out_office_name')->nullable()->after('check_out_office_radius');

            // Audit: was the device's automatic date & time enabled at capture.
            $table->boolean('check_in_auto_time')->nullable()->after('check_out_office_name');
            $table->boolean('check_out_auto_time')->nullable()->after('check_in_auto_time');
        });

        $this->createClientIdUniqueIndexes();
    }

    public function down(): void
    {
        $this->dropClientIdUniqueIndexes();

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropConstrainedForeignId('check_in_office_id');
            $table->dropConstrainedForeignId('check_out_office_id');

            $table->dropColumn([
                'check_in_client_id',
                'check_out_client_id',
                'check_in_captured_at',
                'check_out_captured_at',
                'check_in_synced_at',
                'check_out_synced_at',
                'check_in_office_latitude',
                'check_in_office_longitude',
                'check_in_office_radius',
                'check_in_office_name',
                'check_out_office_latitude',
                'check_out_office_longitude',
                'check_out_office_radius',
                'check_out_office_name',
                'check_in_auto_time',
                'check_out_auto_time',
            ]);
        });
    }

    /**
     * Unique index on the idempotency keys, ignoring NULLs.
     *
     * SQL Server needs a filtered index because it treats NULLs as equal; the
     * other supported drivers (MySQL, PostgreSQL, SQLite) already allow many
     * NULLs in a plain unique index.
     */
    private function createClientIdUniqueIndexes(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlsrv') {
            DB::statement('CREATE UNIQUE INDEX attendances_check_in_client_id_unique ON attendances (check_in_client_id) WHERE check_in_client_id IS NOT NULL');
            DB::statement('CREATE UNIQUE INDEX attendances_check_out_client_id_unique ON attendances (check_out_client_id) WHERE check_out_client_id IS NOT NULL');

            return;
        }

        Schema::table('attendances', function (Blueprint $table) {
            $table->unique('check_in_client_id');
            $table->unique('check_out_client_id');
        });
    }

    private function dropClientIdUniqueIndexes(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlsrv') {
            DB::statement('DROP INDEX attendances_check_in_client_id_unique ON attendances');
            DB::statement('DROP INDEX attendances_check_out_client_id_unique ON attendances');

            return;
        }

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropUnique('attendances_check_in_client_id_unique');
            $table->dropUnique('attendances_check_out_client_id_unique');
        });
    }
};
