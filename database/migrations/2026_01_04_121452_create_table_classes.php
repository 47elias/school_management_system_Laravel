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
        Schema::create('school_classes', function (Blueprint $table) {
            $table->id();
            $table->string('class_name'); // e.g., Grade 1A, Form 4 North
            $table->string('class_code')->unique(); // e.g., G1A, F4N
            $table->string('room_number')->nullable();
            $table->integer('capacity')->default(100);
            $table->unsignedBigInteger('teacher_id')->nullable(); // Foreign key for staff/teachers
            $table->string('status')->default('active'); // active, inactive
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_classes');
    }
};
