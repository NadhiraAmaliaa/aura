<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Master data table for study programs (program studi). Each program is
     * optionally linked to a university so the program autocomplete can be
     * filtered by the selected university. The link is nullable because the
     * source dataset contains programs whose university is not present in the
     * universities table.
     */
    public function up(): void
    {
        Schema::create('study_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('university_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('level', 20)->nullable();
            // Soft-disable flag: inactive study programs are hidden from the
            // autocomplete but remain available for existing intern records.
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('name');
            $table->index(['university_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('study_programs');
    }
};
