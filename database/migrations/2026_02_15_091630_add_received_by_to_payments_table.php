<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('payments', function (Blueprint $table) {
        // Adding as an unsigned big integer to link to the users table
        $table->unsignedBigInteger('received_by')->nullable()->after('reference_no');

        // Optional: Add a foreign key constraint if you want database-level integrity
        $table->foreign('received_by')->references('id')->on('users')->onDelete('set null');
    });
}

public function down()
{
    Schema::table('payments', function (Blueprint $table) {
        $table->dropForeign(['received_by']); // Drop constraint first
        $table->dropColumn('received_by');
    });
}
};
