<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('exams', 'max_marks')) {
            Schema::table('exams', function (Blueprint $table) {
                $table->decimal('max_marks', 8, 2)->default(100)->after('exam_date');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('exams', 'max_marks')) {
            Schema::table('exams', function (Blueprint $table) {
                $table->dropColumn('max_marks');
            });
        }
    }
};
