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
    Schema::table('admissions', function (Blueprint $table) {
        // Add columns if they don't exist to prevent errors
        if (!Schema::hasColumn('admissions', 'address')) {
            $table->text('address')->nullable()->after('applied_grade');
        }
        if (!Schema::hasColumn('admissions', 'previous_school')) {
            $table->string('previous_school')->nullable()->after('guardian_email');
        }
        // Ensure guardian_email exists (mapped from $request->email)
        if (!Schema::hasColumn('admissions', 'guardian_email')) {
            $table->string('guardian_email')->nullable()->after('guardian_phone');
        }
    });
}

public function down(): void
{
    Schema::table('admissions', function (Blueprint $table) {
        $table->dropColumn(['address', 'previous_school', 'guardian_email']);
    });
}
};
