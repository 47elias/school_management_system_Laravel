<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')
                ->constrained('students')
                ->cascadeOnDelete();
            $table->foreignId('term_id')->constrained('terms');
            $table->decimal('amount', 10, 2);
            // paynow_link, ecocash_push, card
            $table->string('channel');
            // pending, paid, failed
            $table->string('status')->default('pending');
            // Paynow's poll url, used to re-check transaction status
            $table->text('poll_url')->nullable();
            // Paynow's own reference for this transaction, once returned
            $table->string('paynow_reference')->nullable();
            $table->string('payer_phone')->nullable();
            $table->string('payer_email')->nullable();
            $table->text('remarks')->nullable();
            // Set once a matching row is created in `payments` after confirmation
            $table->foreignId('payment_id')
                ->nullable()
                ->constrained('payments')
                ->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_transactions');
    }
};
