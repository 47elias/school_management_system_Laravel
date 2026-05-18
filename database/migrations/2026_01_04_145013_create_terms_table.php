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
        Schema::create('terms', function (Blueprint $table) {
            $table->id();
            $table->string('term_name'); // e.g., Term 1, First Term
            $table->string('academic_year'); // e.g., 2026
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_current')->default(false); // Only one term can be active
            $table->string('status')->default('open'); // open, closed
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('terms');
    }
};
