<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Internal users (admin/supervisor) gain an activation flag so their
     * accounts can be deactivated without deletion, and supervisors are linked
     * to the single division whose data they are allowed to manage. The column
     * is nullable because admins and interns are not tied to a division.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('password');
            $table->foreignId('division_id')
                ->nullable()
                ->after('is_active')
                ->constrained('divisions')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('division_id');
            $table->dropColumn('is_active');
        });
    }
};
