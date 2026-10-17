<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grade_scales', function (Blueprint $table) {
            $table->id();
            $table->string('grade');
            $table->decimal('min_score', 5, 2);
            $table->decimal('max_score', 5, 2);
            $table->string('remark')->nullable();
            $table->timestamps();
        });

        // Default initial grade scale seeding
        DB::table('grade_scales')->insert([
            ['grade' => 'A', 'min_score' => 80.00, 'max_score' => 100.00, 'remark' => 'Distinction', 'created_at' => now(), 'updated_at' => now()],
            ['grade' => 'B', 'min_score' => 70.00, 'max_score' => 79.99,  'remark' => 'Merit',       'created_at' => now(), 'updated_at' => now()],
            ['grade' => 'C', 'min_score' => 60.00, 'max_score' => 69.99,  'remark' => 'Credit',      'created_at' => now(), 'updated_at' => now()],
            ['grade' => 'D', 'min_score' => 50.00, 'max_score' => 59.99,  'remark' => 'Pass',        'created_at' => now(), 'updated_at' => now()],
            ['grade' => 'F', 'min_score' => 0.00,  'max_score' => 49.99,  'remark' => 'Fail',        'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_scales');
    }
};