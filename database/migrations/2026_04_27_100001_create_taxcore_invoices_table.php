<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('taxcore_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('sale_id')->nullable()->comment('Local sale reference');
            $table->string('taxcore_uid')->nullable()->unique()->comment('UUID returned by TaxCore');
            $table->text('qr_code')->nullable();
            $table->text('digital_signature')->nullable();
            $table->string('verification_url')->nullable();
            $table->json('raw_response')->nullable()->comment('Full JSON response from TaxCore');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('taxcore_invoices');
    }
};
