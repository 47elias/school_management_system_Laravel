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
        Schema::create('class_subject', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_class_id'); // Matches your model/controller logic
            $table->unsignedBigInteger('subject_id');      // Matches your model/controller logic
            $table->timestamps();

            // Corrected Foreign Key: references 'school_classes' table defined earlier
            $table->foreign('school_class_id')
                  ->references('id')
                  ->on('school_classes')
                  ->onDelete('cascade');

            // References 'subjects' table
            $table->foreign('subject_id')
                  ->references('id')
                  ->on('subjects')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_subject');
    }
};
