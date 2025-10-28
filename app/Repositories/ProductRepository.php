<?php

namespace App\Repositories;

use App\Models\Coin;
use App\Models\Measure;
use App\Models\Product;
use App\Models\SubBrand;
use App\Models\Supplier;

class ProductRepository
{
    private Product $product;

    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    public function getAll()
    {
        return $this->product
            ->with(['supplierCoin', 'landingCoin', 'measure', 'subBrand.brand', 'supplier'])
            ->get();
    }

    public function find(int $id)
    {
        return $this->product
            ->with(['supplierCoin', 'landingCoin', 'measure', 'subBrand.brand', 'supplier'])
            ->find($id);
    }

    public function create(array $data, Coin $supplierCoin, Coin $landingCoin,
                           Measure $measure, SubBrand $subBrand, Supplier $supplier)
    {
        $product = Product::at($data['code'], $data['supplier_cost_price'], $supplierCoin,
            $data['landing_cost_price'], $landingCoin, $data['retail_price'], $data['promotional_price'], $data['stock'],
            $measure, $data['serial_tracking'], $data['dimension_size'], $data['dimension_weight'], $subBrand, $supplier
        );

        $product->save();

        return $product->load(['supplierCoin', 'landingCoin', 'measure', 'subBrand.brand', 'supplier']);
    }

    public function update(Product $product, array $data, Coin $supplierCoin, Coin $landingCoin,
                           Measure $measure, SubBrand $subBrand, Supplier $supplier)
    {
        Product::at($data['code'], $data['supplier_cost_price'], $supplierCoin,
            $data['landing_cost_price'], $landingCoin, $data['retail_price'], $data['promotional_price'], $data['stock'],
            $measure, $data['serial_tracking'], $data['dimension_size'], $data['dimension_weight'], $subBrand, $supplier
        );

        $product->code = $data['code'];
        $product->supplier_cost_price = $data['supplier_cost_price'];
        $product->supplier_coin_id = $supplierCoin->id;
        $product->landing_cost_price = $data['landing_cost_price'];
        $product->landing_coin_id = $landingCoin->id;
        $product->retail_price = $data['retail_price'];
        $product->promotional_price = $data['promotional_price'];
        $product->stock = $data['stock'];
        $product->measure_id = $measure->id;
        $product->serial_tracking = $data['serial_tracking'];
        $product->dimension_size = $data['dimension_size'];
        $product->dimension_weight = $data['dimension_weight'];
        $product->sub_brand_id = $subBrand->id;
        $product->supplier_id = $supplier->id;

        $product->save();

        return $product->load(['supplierCoin', 'landingCoin', 'measure', 'subBrand.brand', 'supplier']);
    }
}
