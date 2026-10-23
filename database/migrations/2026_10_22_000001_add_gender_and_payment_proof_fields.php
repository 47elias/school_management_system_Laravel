<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('admissions', 'gender')) {
            Schema::table('admissions', function (Blueprint $table) {
                $table->string('gender')->nullable();
            });
        }

        if (!Schema::hasColumn('admissions', 'activation_payment_proof')) {
            Schema::table('admissions', function (Blueprint $table) {
                $table->string('activation_payment_proof')->nullable();
                $table->string('activation_payment_status')->nullable()->index();
                $table->text('activation_payment_remarks')->nullable();
                $table->string('portal_password')->nullable();
            });
        }

        if (!Schema::hasColumn('students', 'activation_payment_proof')) {
            Schema::table('students', function (Blueprint $table) {
                $table->string('activation_payment_proof')->nullable();
                $table->string('activation_payment_status')->nullable()->index();
                $table->text('activation_payment_remarks')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('admissions', 'gender')) {
            Schema::table('admissions', fn (Blueprint $table) => $table->dropColumn('gender'));
        }

        if (Schema::hasColumn('admissions', 'activation_payment_proof')) {
            Schema::table('admissions', function (Blueprint $table) {
                $table->dropColumn(['activation_payment_proof', 'activation_payment_status', 'activation_payment_remarks', 'portal_password']);
            });
        }

        if (Schema::hasColumn('students', 'activation_payment_proof')) {
            Schema::table('students', function (Blueprint $table) {
                $table->dropColumn(['activation_payment_proof', 'activation_payment_status', 'activation_payment_remarks']);
            });
        }
    }
};
