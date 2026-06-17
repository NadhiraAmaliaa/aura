<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Master data table for universities (perguruan tinggi). It backs the
     * searchable autocomplete used when registering interns and is the
     * authoritative source for an intern's university reference.
     */
    public function up(): void
    {
        Schema::create('universities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('lldikti', 20)->nullable();
            // Soft-disable flag: inactive universities are hidden from the
            // autocomplete but remain available for existing intern records.
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Supports prefix LIKE lookups for the autocomplete field. SQL Server
            // can use this index for "name LIKE 'foo%'" searches.
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('universities');
    }
};
