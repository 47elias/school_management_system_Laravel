<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->onDelete('cascade');
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('verified_by')->constrained('users')->onDelete('cascade'); // The invigilator/teacher
            $table->string('verification_method')->default('face_scan');
            $table->timestamp('verified_at');
            $table->enum('status', ['present', 'flagged', 'absent'])->default('present');
            $table->text('notes')->nullable(); // For adjustments or manual overrides
            $table->timestamps();

            // Prevent duplicate entries for the same student in the same exam
            $table->unique(['exam_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_attendances');
    }
};
