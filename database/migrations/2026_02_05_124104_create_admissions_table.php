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
        Schema::create('admissions', function (Blueprint $table) {
            $table->id();
            
            // Unique Tracking ID (National ID or Birth No)
            $table->string('tracking_id')->unique()->index();
            
            // Student Details
            $table->string('student_name');
            $table->date('date_of_birth');
            $table->string('applied_grade');
            
            // Guardian Details
            $table->string('guardian_name');
            $table->string('guardian_phone');
            $table->string('guardian_email')->nullable();
            $table->text('address')->nullable();
            
            // Internal Tracking
            $table->enum('status', ['pending', 'reviewing', 'interview', 'approved', 'rejected'])
                  ->default('pending');
            $table->text('admin_remarks')->nullable(); // For internal notes
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admissions');
    }
};
