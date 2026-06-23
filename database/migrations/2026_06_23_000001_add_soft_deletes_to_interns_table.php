<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Soft deletes let an administrator archive an intern (hiding it from the
     * default participant list) without ever destroying the record, so the
     * linked attendance history, leave requests, reports and exports stay
     * intact and the intern can be restored later.
     */
    public function up(): void
    {
        Schema::table('interns', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('interns', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
