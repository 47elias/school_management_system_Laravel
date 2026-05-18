<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            // Adds the missing column that the error is complaining about
            $table->unsignedBigInteger('class_id')->nullable()->after('id');

            // Adds a foreign key to link it properly to school_classes
            $table->foreign('class_id')->references('id')->on('school_classes')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['class_id']);
            $table->dropColumn('class_id');
        });
    }
};
