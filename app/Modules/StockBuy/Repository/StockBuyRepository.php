<?php

namespace App\Modules\StockBuy\Repository;

use App\Modules\Product\Domain\Product;
use App\Modules\StockBuy\Domain\StockBuy;
use Illuminate\Support\Facades\DB;

class StockBuyRepository
{
    private StockBuy $stockBuy;

    public function __construct(StockBuy $stockBuy)
    {
        $this->stockBuy = $stockBuy;
    }

    public function getAll()
    {
        return $this->stockBuy
            ->with(['product.supplierCoin', 'product.landingCoin', 'product.measure', 'product.subBrand.brand',
                'product.supplier'])->get();
    }

    public function find(int $id)
    {
        return $this->stockBuy->with(['product.supplierCoin', 'product.landingCoin', 'product.measure',
            'product.subBrand.brand', 'product.supplier'])->find($id);
    }

    public function create(array $data, Product $product)
    {
        $stockBuy = StockBuy::at($product, $data['amount'], $data['date'], $data['description']);
        $stockBuy->save();

        return $stockBuy->load(['product.supplierCoin', 'product.landingCoin', 'product.measure',
            'product.subBrand.brand', 'product.supplier']);
    }

    public function update(StockBuy $stockBuy, array $data)
    {
        $validated = StockBuy::at($stockBuy->product, $data['amount'], $data['date'], $data['description']);

        $stockBuy->update([
            'amount' => $validated->amount,
            'date' => $validated->date,
            'description' => $validated->description,
        ]);

        return $stockBuy->load([
            'product.supplierCoin', 'product.landingCoin', 'product.measure',
            'product.subBrand.brand', 'product.supplier',
        ]);
    }
}
