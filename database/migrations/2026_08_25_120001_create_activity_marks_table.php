<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_marks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('class_activity_id')
                ->constrained('class_activities')
                ->cascadeOnDelete();

            $table->foreignId('student_id')
                ->constrained('students')
                ->cascadeOnDelete();

            $table->decimal('score', 6, 2);
            $table->string('comment')->nullable();

            $table->timestamps();

            // One score per student per activity. This also lets the controller
            // use a single upsert/updateOrCreate call safely instead of
            // check-then-write, which is both simpler and race-condition-free.
            $table->unique(['class_activity_id', 'student_id'], 'activity_marks_unique');
            $table->index('student_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_marks');
    }
};
