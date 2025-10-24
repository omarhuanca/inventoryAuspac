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
        Schema::create('product', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->decimal('supplier_cost', 10, 2);
            $table->foreignId('supplier_cost_price_id')->constrained('coin')->onUpdate('cascade')->onDelete('restrict');
            $table->foreignId('supplier_coin_id')->constrained('coin')->onUpdate('cascade')->onDelete('restrict');
            $table->decimal('landing_cost_price', 10, 2);
            $table->foreignId('landing_coin_id')->constrained('coin')->onUpdate('cascade')->onDelete('restrict');
            $table->decimal('retail_price', 10, 2);
            $table->decimal('promotional_price', 10, 2);
            $table->integer('stock');
            $table->foreignId('measure_id')->constrained('measure')->onUpdate('cascade')->onDelete('restrict');
            $table->string('serial_tracking');
            $table->string('dimension_size');
            $table->integer('dimension_weight');
            $table->foreignId('sub_brand_id')->constrained('sub_brand')->onUpdate('cascade')->onDelete('restrict');
            $table->foreignId('supplier_id')->constrained('supplier')->onUpdate('cascade')->onDelete('restrict');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product');
    }
};
