<?php

// database/migrations/2026_10_16_000001_create_grade_levels_and_update_classes.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Grade Levels / Forms Catalog
        Schema::create('grade_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name');              // e.g. "Form 1", "Form 2", "Lower 6"
            $table->integer('level_order');      // e.g. 1, 2, 3, 4, 5, 6 (for sorting)
            $table->timestamps();
        });

        // Seed default secondary school forms
        DB::table('grade_levels')->insert([
            ['name' => 'Form 1', 'level_order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Form 2', 'level_order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Form 3', 'level_order' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Form 4', 'level_order' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Lower 6', 'level_order' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Upper 6', 'level_order' => 6, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 2. Link school_classes to grade_levels and add Stream field
        Schema::table('school_classes', function (Blueprint $table) {
            if (!Schema::hasColumn('school_classes', 'grade_level_id')) {
                $table->foreignId('grade_level_id')->nullable()->after('id')->constrained('grade_levels')->onDelete('cascade');
            }
            if (!Schema::hasColumn('school_classes', 'stream')) {
                $table->string('stream')->nullable()->after('class_name'); // e.g. "A", "B", "East", "West"
            }
        });
    }

    public function down(): void
    {
        Schema::table('school_classes', function (Blueprint $table) {
            $table->dropForeign(['grade_level_id']);
            $table->dropColumn(['grade_level_id', 'stream']);
        });
        Schema::dropIfExists('grade_levels');
    }
};