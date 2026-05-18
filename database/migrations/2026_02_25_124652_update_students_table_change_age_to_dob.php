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
        Schema::table('students', function (Blueprint $blueprint) {
            // Adding date_of_birth after the surname column
            $blueprint->date('date_of_birth')->nullable()->after('surname');

            // Removing the age column
            $blueprint->dropColumn('age');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $blueprint) {
            // Restoring the age column
            $blueprint->integer('age')->after('surname');

            // Removing the date_of_birth column
            $blueprint->dropColumn('date_of_birth');
        });
    }
};
