<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payslips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('pay_period'); // e.g., "February 2026"
            $table->decimal('base_salary', 10, 2);
            $table->decimal('allowances', 10, 2)->default(0.00);
            $table->decimal('deductions', 10, 2)->default(0.00);
            $table->decimal('net_salary', 10, 2);
            $table->date('payment_date');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payslips');
    }
};
