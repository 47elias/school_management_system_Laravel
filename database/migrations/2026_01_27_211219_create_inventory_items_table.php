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
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->string('item_name');
            $table->string('sku')->unique(); // Stock Keeping Unit / Code
            $table->string('category')->nullable(); // Stationery, Uniform, Furniture
            $table->integer('quantity')->default(0); // Current Stock Level
            $table->integer('alert_level')->default(5); // Notify when stock is low
            $table->decimal('unit_price', 10, 2)->default(0.00);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
