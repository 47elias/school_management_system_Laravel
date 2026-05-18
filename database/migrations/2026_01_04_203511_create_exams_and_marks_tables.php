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
        // 1. Exam Definitions (e.g., "Term 1 Final Exams 2026")
    Schema::create('exams', function (Blueprint $table) {
        $table->id();
        $table->string('exam_name');
        $table->unsignedBigInteger('term_id');
        $table->date('exam_date');
        $table->enum('status', ['pending', 'published'])->default('pending');
        $table->foreign('term_id')->references('id')->on('terms');
        $table->timestamps();
    });

    // 2. Marks Entry
    Schema::create('marks', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('exam_id');
        $table->unsignedBigInteger('student_id');
        $table->string('subject'); // e.g., Math, English, Science
        $table->integer('score');
        $table->integer('max_score')->default(100);
        $table->text('teacher_comment')->nullable();

        $table->foreign('exam_id')->references('id')->on('exams')->onDelete('cascade');
        $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exams_and_marks_tables');
    }
};
