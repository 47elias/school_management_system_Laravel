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
       Schema::table('students', function (Blueprint $table) {
        // Adds the missing column
        $table->unsignedBigInteger('class_id')->nullable()->after('id');

        // Optional: Add the foreign key constraint
        $table->foreign('class_id')->references('id')->on('school_classes')->onDelete('set null');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            //
        });
    }
};
