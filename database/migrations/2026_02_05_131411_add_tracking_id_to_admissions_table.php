<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admissions', function (Blueprint $table) {
            // We add it after the 'id' column for a clean structure
            $table->string('tracking_id')->unique()->after('id')->index();
            
            // Just in case these are missing too based on your previous controller error:
            if (!Schema::hasColumn('admissions', 'subjects_passed')) {
                $table->text('subjects_passed')->nullable()->after('guardian_phone');
            }
            if (!Schema::hasColumn('admissions', 'results_file')) {
                $table->string('results_file')->nullable()->after('subjects_passed');
            }
            if (!Schema::hasColumn('admissions', 'recommendation_letter')) {
                $table->string('recommendation_letter')->nullable()->after('results_file');
            }
        });
    }

    public function down(): void
    {
        Schema::table('admissions', function (Blueprint $table) {
            $table->dropColumn(['tracking_id', 'subjects_passed', 'results_file', 'recommendation_letter']);
        });
    }
};