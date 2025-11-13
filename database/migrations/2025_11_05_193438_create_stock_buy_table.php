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
        Schema::create('stock_buy', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('product')->onUpdate('cascade')->onDelete('restrict');
            $table->integer('amount');
            $table->date('date');
            $table->string('description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_buy');
    }
};
