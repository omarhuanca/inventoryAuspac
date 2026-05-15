<?php

namespace App\Modules\StockReferral\Repository;

use App\Modules\Product\Domain\Product;
use App\Modules\StockReferral\Domain\StockReferral;

class StockReferralRepository
{
    private StockReferral $stockReferral;

    public function __construct(StockReferral $stockReferral)
    {
        $this->stockReferral = $stockReferral;
    }

    public function getAll()
    {
        return $this->stockReferral
            ->with(['product.supplierCoin', 'product.landingCoin', 'product.measure',
                'product.subBrand.brand', 'product.supplier'])
            ->get();
    }

    public function find(int $id)
    {
        return $this->stockReferral
            ->with(['product.supplierCoin', 'product.landingCoin', 'product.measure',
                'product.subBrand.brand', 'product.supplier'])
            ->find($id);
    }

    public function create(array $data, Product $product)
    {
        $stockReferral = StockReferral::at($product, $data['amount'], $data['date']);
        $stockReferral->save();

        return $stockReferral->load(['product.supplierCoin', 'product.landingCoin', 'product.measure',
            'product.subBrand.brand', 'product.supplier']);
    }

    public function update(StockReferral $stockReferral, array $data)
    {
        $validated = StockReferral::at($stockReferral->product, $data['amount'], $data['date']);

        $stockReferral->update([
            'amount' => $validated->amount,
            'date' => $validated->date,
        ]);

        return $stockReferral->load(['product.supplierCoin', 'product.landingCoin', 'product.measure',
            'product.subBrand.brand', 'product.supplier']);
    }

    public function delete(StockReferral $stockReferral): void
    {
        $stockReferral->delete();
    }
}
