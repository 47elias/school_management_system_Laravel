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
        Schema::table('timetables', function (Blueprint $table) {
            $table->unsignedBigInteger('subject_id')->nullable()->change();
            $table->unsignedBigInteger('teacher_id')->nullable()->change();
            // Optional: Add a 'type' column to store "BREAK" or "LUNCH"
            if (!Schema::hasColumn('timetables', 'type')) {
                $table->string('type')->default('SUBJECT')->after('end_time');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('timetables', function (Blueprint $table) {
            //
        });
    }
};
