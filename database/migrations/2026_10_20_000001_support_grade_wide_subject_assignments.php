<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subject_assignments', function (Blueprint $table) {
            if (!Schema::hasColumn('subject_assignments', 'grade')) {
                $table->string('grade')->nullable()->after('id');
            }

            $table->foreignId('teacher_id')->nullable()->change();
            $table->foreignId('class_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Keep nullable grade-wide allocations intact when rolling back code.
    }
};
