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
    // 1. Fee Structure (e.g., Tuition Fee for Term 1)
    Schema::create('fee_structures', function (Blueprint $table) {
        $table->id();
        $table->string('fee_name'); // e.g., Tuition, Uniform, Lab Fee
        $table->decimal('amount', 10, 2);
        $table->string('grade'); // Grade specific fees
        $table->unsignedBigInteger('term_id');
        $table->foreign('term_id')->references('id')->on('terms');
        $table->timestamps();
    });

    // 2. Individual Student Payments
    Schema::create('payments', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('student_id');
        $table->unsignedBigInteger('term_id');
        $table->decimal('amount_paid', 10, 2);
        $table->date('payment_date');
        $table->string('payment_method'); // Cash, Bank Transfer, Card
        $table->string('reference_no')->nullable();
        $table->text('remarks')->nullable();

        $table->foreign('student_id')->references('id')->on('students');
        $table->foreign('term_id')->references('id')->on('terms');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fees_and_payments_tables');
    }
};
