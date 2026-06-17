<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('working_hours', function (Blueprint $table) {
            $table->id();
            // ISO day-of-week: 1 = Monday ... 7 = Sunday. One row per day.
            $table->unsignedTinyInteger('day_of_week')->unique();
            // Stored as "HH:MM" strings to stay portable across SQL Server and
            // SQLite (used in tests). Null when the day is non-working.
            $table->string('start_time', 5)->nullable();
            $table->string('end_time', 5)->nullable();
            $table->boolean('is_working_day')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('working_hours');
    }
};
