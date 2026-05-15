<?php

namespace App\Modules\TaxCore\Repository;

use App\Modules\TaxCore\Domain\TaxCoreInvoice;

class TaxCoreInvoiceRepository
{
    public function store(array $data): TaxCoreInvoice
    {
        return TaxCoreInvoice::create($data);
    }

    /**
     * Create a pending invoice record before dispatching the job.
     */
    public function createPending(array $data): TaxCoreInvoice
    {
        return TaxCoreInvoice::create(array_merge([
            'status'     => 'pending',
            'queued_at'  => now(),
            'attempts'   => 0,
        ], $data));
    }

    /**
     * Update status and any additional fields atomically.
     */
    public function updateStatus(TaxCoreInvoice $invoice, string $status, array $extra = []): TaxCoreInvoice
    {
        $invoice->update(array_merge(['status' => $status], $extra));
        return $invoice->fresh();
    }

    public function findById(int $id): ?TaxCoreInvoice
    {
        return TaxCoreInvoice::find($id);
    }

    public function findBySaleId(string $saleId): ?TaxCoreInvoice
    {
        return TaxCoreInvoice::where('sale_id', $saleId)->latest()->first();
    }

    public function findByJobId(string $jobId): ?TaxCoreInvoice
    {
        return TaxCoreInvoice::where('queue_job_id', $jobId)->first();
    }

    public function all(int $perPage = 20)
    {
        return TaxCoreInvoice::latest()->paginate($perPage);
    }
}
