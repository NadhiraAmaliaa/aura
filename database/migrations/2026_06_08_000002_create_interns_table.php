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
        Schema::create('interns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('intern_program_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('nim');
            $table->string('phone');
            $table->string('university');
            $table->string('major');
            $table->string('division');
            $table->date('start_date');
            $table->date('end_date');
            // Stored as a plain string (not enum) for SQL Server portability:
            // an enum becomes a CHECK constraint, which rejects new status
            // values such as "upcoming". Allowed values are enforced in the
            // Intern model (STATUS_* constants / effectiveStatus()).
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interns');
    }
};
