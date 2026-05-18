<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('subject_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->foreignId('class_id')->constrained('school_classes')->onDelete('cascade');
            $table->string('academic_year');
            $table->timestamps();

            // Prevent duplicate assignments (same teacher, same subject, same class)
            $table->unique(['teacher_id', 'subject_id', 'class_id'], 'teacher_subject_class_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('subject_assignments');
    }
};
