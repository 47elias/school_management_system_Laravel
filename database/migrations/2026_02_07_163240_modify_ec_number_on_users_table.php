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
        Schema::table('users', function (Blueprint $table) {
            // This makes ec_number nullable so students don't need one
            $table->string('ec_number')->nullable()->change();

            // Also making employee_id nullable just in case for future student accounts
            $table->string('employee_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('ec_number')->nullable(false)->change();
            $table->string('employee_id')->nullable(false)->change();
        });
    }
};
