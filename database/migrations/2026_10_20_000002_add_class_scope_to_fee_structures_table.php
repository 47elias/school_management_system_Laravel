<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fee_structures', function (Blueprint $table) {
            if (!Schema::hasColumn('fee_structures', 'class_id')) {
                $table->foreignId('class_id')->nullable()->after('student_id')
                    ->constrained('school_classes')->nullOnDelete();
            }

            if (!Schema::hasColumn('fee_structures', 'charge_timing')) {
                $table->string('charge_timing')->default('during_term')->after('class_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('fee_structures', function (Blueprint $table) {
            if (Schema::hasColumn('fee_structures', 'class_id')) {
                $table->dropConstrainedForeignId('class_id');
            }

            if (Schema::hasColumn('fee_structures', 'charge_timing')) {
                $table->dropColumn('charge_timing');
            }
        });
    }
};
