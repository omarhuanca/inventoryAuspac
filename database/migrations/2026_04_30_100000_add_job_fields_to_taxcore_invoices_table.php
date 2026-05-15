<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('taxcore_invoices', function (Blueprint $table) {
            $table->string('status')->default('pending')->after('id')
                ->comment('pending | processing | completed | failed | dead_lettered');
            $table->string('queue_job_id')->nullable()->after('status')
                ->comment('Laravel job UUID dispatched for this invoice');
            $table->string('esdc_id')->nullable()->after('queue_job_id')
                ->comment('E-SDC instance identifier (for multi-branch routing)');
            $table->unsignedTinyInteger('attempts')->default(0)->after('esdc_id');
            $table->text('error_message')->nullable()->after('attempts');
            $table->timestamp('queued_at')->nullable()->after('error_message');
            $table->timestamp('processed_at')->nullable()->after('queued_at');
        });
    }

    public function down(): void
    {
        Schema::table('taxcore_invoices', function (Blueprint $table) {
            $table->dropColumn([
                'status',
                'queue_job_id',
                'esdc_id',
                'attempts',
                'error_message',
                'queued_at',
                'processed_at',
            ]);
        });
    }
};
