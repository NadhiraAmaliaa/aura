<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Make the intern phone number nullable.
     *
     * Phone numbers are now self-managed contact data: the admin creates the
     * account without one and the intern fills it in after logging in. The
     * column is therefore nullable.
     */
    public function up(): void
    {
        Schema::table('interns', function (Blueprint $table) {
            $table->string('phone')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('interns', function (Blueprint $table) {
            $table->string('phone')->nullable(false)->change();
        });
    }
};
