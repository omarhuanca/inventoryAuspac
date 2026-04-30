<?php

namespace App\Modules\TaxCore\Repository;

use App\Modules\TaxCore\Domain\TaxCoreInvoice;

class TaxCoreInvoiceRepository
{
    public function store(array $data): TaxCoreInvoice
    {
        return TaxCoreInvoice::create($data);
    }

    public function findBySaleId(string $saleId): ?TaxCoreInvoice
    {
        return TaxCoreInvoice::where('sale_id', $saleId)->latest()->first();
    }

    public function findById(int $id): ?TaxCoreInvoice
    {
        return TaxCoreInvoice::find($id);
    }

    public function all(int $perPage = 20)
    {
        return TaxCoreInvoice::latest()->paginate($perPage);
    }
}
