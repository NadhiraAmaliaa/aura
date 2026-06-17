<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Move the intern's university, study program and division from free-text
     * columns to references into the new master-data tables. The original text
     * columns are kept (nullable) so existing records remain readable and can
     * be back-filled, but new records populate the reference columns.
     *
     * The intern login identity becomes the combination of university and NIM,
     * because a NIM is only unique within a single university. The previous
     * global unique index on `nim` (created implicitly via the store request,
     * not the schema) is therefore replaced by a composite unique index.
     */
    public function up(): void
    {
        Schema::table('interns', function (Blueprint $table) {
            $table->foreignId('university_id')
                ->nullable()
                ->after('intern_program_id')
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('study_program_id')
                ->nullable()
                ->after('university_id')
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('division_id')
                ->nullable()
                ->after('study_program_id')
                ->constrained()
                ->nullOnDelete();
        });

        // Make the legacy free-text columns nullable; they are no longer the
        // primary source of truth but are retained for historical readability.
        Schema::table('interns', function (Blueprint $table) {
            $table->string('university')->nullable()->change();
            $table->string('major')->nullable()->change();
            $table->string('division')->nullable()->change();
        });

        // A NIM uniquely identifies an intern only within one university.
        Schema::table('interns', function (Blueprint $table) {
            $table->unique(['university_id', 'nim'], 'interns_university_id_nim_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('interns', function (Blueprint $table) {
            $table->dropUnique('interns_university_id_nim_unique');
        });

        Schema::table('interns', function (Blueprint $table) {
            $table->dropConstrainedForeignId('university_id');
            $table->dropConstrainedForeignId('study_program_id');
            $table->dropConstrainedForeignId('division_id');
        });
    }
};
