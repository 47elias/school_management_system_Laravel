<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // 1. Rename the column
            $table->renameColumn('employee_number', 'dob');
        });

        Schema::table('users', function (Blueprint $table) {
            // 2. Change the type to date
            // Note: This will work best if the existing column is empty.
            // If it contains data, you may need to clear it first.
            $table->date('dob')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('dob', 'employee_number');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('employee_number')->nullable()->change();
        });
    }
};
