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
        Schema::create('bundle', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->decimal('landing_cost_price', 10, 2);
            $table->foreignId('landing_coin_id')->constrained('coin')->onUpdate('cascade')->onDelete('restrict');
            $table->decimal('retail_price', 10, 2);
            $table->decimal('promotional_price', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bundle');
    }
};
