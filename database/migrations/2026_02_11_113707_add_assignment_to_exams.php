<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('exams', function (Blueprint $table) {
            // This links the exam to the specific Teacher + Subject + Class combo
            $table->foreignId('subject_assignment_id')->nullable()->constrained('subject_assignments')->onDelete('cascade');
            // Optional: you can also add class_id directly if you prefer
            $table->foreignId('class_id')->nullable()->constrained('school_classes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            //
        });
    }
};
