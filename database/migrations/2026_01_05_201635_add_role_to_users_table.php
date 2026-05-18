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
        // Default to 'teacher' or 'staff' depending on your needs
        $table->string('role')->default('teacher')->after('email');
        $table->string('phone_number')->nullable()->after('role');
        $table->string('employee_id')->nullable()->unique()->after('id');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
