<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('term_result_publications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('term_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('is_published')->default(false);
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::create('result_fee_exemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('term_id')->constrained()->cascadeOnDelete();
            $table->text('reason');
            $table->foreignId('granted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('revoked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
            $table->index(['student_id', 'term_id', 'revoked_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('result_fee_exemptions');
        Schema::dropIfExists('term_result_publications');
    }
};
