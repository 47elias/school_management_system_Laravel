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
        Schema::create('students', function (Blueprint $table) {
  $table->id(); // Primary key for internal database use
        $table->string('student_number')->unique(); // This will store EA1001
        $table->string('name');
        $table->string('surname');
        $table->integer('age');
        $table->string('gender')->nullable();
        $table->string('national_id')->nullable(); // National identification number
        $table->string('grade')->nullable();
        $table->text('address')->nullable();
        $table->string('parent_contact')->nullable();
        $table->string('email')->nullable();
        $table->string('phone')->nullable();
        $table->date('enrollment_date')->nullable();
        $table->string('status')->default('active'); // active, inactive, graduated, etc.
        $table->string('photo_path')->nullable(); // Path to the student's photo
        $table->string('emergency_contact')->nullable();
        $table->string('password')->nullable(); // For student portal access
        $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
