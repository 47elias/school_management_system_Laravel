<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // 1. Make the columns nullable
            $table->string('ec_number')->nullable()->change();
            $table->string('employee_id')->nullable()->change();
            $table->string('employee_number')->nullable()->change();

            // 2. Add national_id if it's missing (it was in your dump, but good to be safe)
            if (!Schema::hasColumn('users', 'national_id')) {
                $table->string('national_id')->nullable()->after('email');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('ec_number')->nullable(false)->change();
            $table->string('employee_id')->nullable(false)->change();
        });
    }
};
