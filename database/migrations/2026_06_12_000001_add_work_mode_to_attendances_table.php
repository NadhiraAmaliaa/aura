<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds the attendance work mode (WFO / WFH / Dinas). A plain string column
     * is used instead of an enum so the schema stays portable across database
     * engines (including SQL Server) and so that adding a future mode does not
     * require dropping a check constraint. Allowed values are enforced in the
     * application layer.
     */
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->string('work_mode', 20)->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn('work_mode');
        });
    }
};
