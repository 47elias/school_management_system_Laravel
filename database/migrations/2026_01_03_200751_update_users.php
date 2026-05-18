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
        Schema::table('users', function (Blueprint $table) {
         $table->string('ec_number')->unique();
        // You can remove $table->email() if you aren't using it
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
