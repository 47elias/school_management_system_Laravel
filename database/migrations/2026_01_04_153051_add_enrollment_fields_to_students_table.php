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
    Schema::table('students', function (Blueprint $table) {
        if (!Schema::hasColumn('students', 'term_id')) {
            $table->unsignedBigInteger('term_id')->nullable()->after('id');
            $table->foreign('term_id')->references('id')->on('terms')->onDelete('set null');
        }

        if (!Schema::hasColumn('students', 'enrollment_date')) {
            $table->date('enrollment_date')->nullable()->after('term_id');
        }

        if (!Schema::hasColumn('students', 'enrollment_status')) {
            $table->string('enrollment_status')->default('active')->after('grade');
        }
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
       Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['term_id']);
            $table->dropColumn(['term_id', 'enrollment_date', 'enrollment_status']);
        });
    }
};
