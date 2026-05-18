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
    Schema::table('admissions', function (Blueprint $table) {
        // Adding identity_number to store the student's actual ID/Birth Cert
        $table->string('identity_number')->unique()->after('tracking_id')->nullable();
    });
}

public function down(): void
{
    Schema::table('admissions', function (Blueprint $table) {
        $table->dropColumn('identity_number');
    });
}
};
