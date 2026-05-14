<?php

namespace App\Modules\TaxCore\Domain;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="TaxCoreInvoice",
 *     type="object",
 *     title="TaxCoreInvoice",
 *     description="Represents a fiscalized invoice sent to the TaxCore E-SDC.",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="status", type="string", example="completed", enum={"pending","processing","completed","failed","dead_lettered"}),
 *     @OA\Property(property="sale_id", type="string", example="SALE-001"),
 *     @OA\Property(property="esdc_id", type="string", example="default"),
 *     @OA\Property(property="taxcore_uid", type="string", example="INV-2026-00001"),
 *     @OA\Property(property="qr_code", type="string", example="https://taxcore.example.com/qr/..."),
 *     @OA\Property(property="digital_signature", type="string", example="ABC123XYZ"),
 *     @OA\Property(property="verification_url", type="string", example="https://taxcore.example.com/verify/..."),
 *     @OA\Property(property="queue_job_id", type="string", format="uuid", nullable=true),
 *     @OA\Property(property="attempts", type="integer", example=1),
 *     @OA\Property(property="error_message", type="string", nullable=true),
 *     @OA\Property(property="queued_at", type="string", format="date-time"),
 *     @OA\Property(property="processed_at", type="string", format="date-time", nullable=true)
 * )
 */
class TaxCoreInvoice extends Model
{
    protected $table = 'taxcore_invoices';

    protected $fillable = [
        'status',
        'queue_job_id',
        'esdc_id',
        'attempts',
        'error_message',
        'queued_at',
        'processed_at',
        'sale_id',
        'taxcore_uid',
        'qr_code',
        'digital_signature',
        'verification_url',
        'raw_response',
    ];

    protected $casts = [
        'raw_response' => 'array',
        'queued_at'    => 'datetime',
        'processed_at' => 'datetime',
    ];

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeFailed($query)
    {
        return $query->whereIn('status', ['failed', 'dead_lettered']);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isPending(): bool    { return $this->status === 'pending'; }
    public function isCompleted(): bool  { return $this->status === 'completed'; }
    public function isFailed(): bool     { return in_array($this->status, ['failed', 'dead_lettered']); }
}
