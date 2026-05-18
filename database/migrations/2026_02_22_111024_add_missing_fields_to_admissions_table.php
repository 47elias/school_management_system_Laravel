<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admissions', function (Blueprint $table) {
            // Adding the columns your Controller is trying to save
            if (!Schema::hasColumn('admissions', 'address')) {
                $table->text('address')->nullable()->after('applied_grade');
            }
            if (!Schema::hasColumn('admissions', 'guardian_email')) {
                $table->string('guardian_email')->nullable()->after('guardian_phone');
            }
            if (!Schema::hasColumn('admissions', 'previous_school')) {
                $table->string('previous_school')->nullable()->after('guardian_email');
            }
        });
    }

    public function down(): void
    {
        Schema::table('admissions', function (Blueprint $table) {
            $table->dropColumn(['address', 'guardian_email', 'previous_school']);
        });
    }
};
