<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $blueprint) {
            $blueprint->decimal('base_salary', 10, 2)->default(0.00)->after('email');
            $blueprint->string('employee_number', 50)->nullable()->after('id');
            $blueprint->string('bank_name', 100)->nullable();
            $blueprint->string('bank_account_no', 100)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $blueprint) {
            $blueprint->dropColumn(['base_salary', 'employee_number', 'bank_name', 'bank_account_no']);
        });
    }
};
