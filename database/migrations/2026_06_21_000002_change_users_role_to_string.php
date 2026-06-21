<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Convert the users.role column from an enum to a plain string.
     *
     * The original enum created a CHECK constraint that only allowed
     * "admin" and "intern", which rejects the new "supervisor" role. A
     * plain string keeps the schema portable and lets the application
     * validate the allowed roles instead of the database. This mirrors the
     * approach already used for the interns.status column.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlsrv') {
            // The enum constraint name is auto-generated, so look it up.
            $constraint = DB::scalar(
                'SELECT cc.name
                 FROM sys.check_constraints cc
                 JOIN sys.columns c
                   ON c.object_id = cc.parent_object_id
                  AND c.column_id = cc.parent_column_id
                 WHERE cc.parent_object_id = OBJECT_ID(?)
                   AND c.name = ?',
                ['users', 'role']
            );

            if ($constraint) {
                DB::statement("ALTER TABLE [users] DROP CONSTRAINT [{$constraint}]");
            }

            DB::statement('ALTER TABLE [users] ALTER COLUMN [role] NVARCHAR(20) NOT NULL');

            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('intern')->change();
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlsrv') {
            DB::statement(
                "ALTER TABLE [users] ADD CONSTRAINT [chk_users_role]
                 CHECK ([role] IN ('admin', 'intern'))"
            );

            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'intern'])->default('intern')->change();
        });
    }
};
