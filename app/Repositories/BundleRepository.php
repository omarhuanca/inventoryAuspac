<?php

namespace App\Repositories;

use App\Models\Bundle;
use App\Models\Coin;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class BundleRepository
{
    private Bundle $bundle;

    public function __construct(Bundle $bundle)
    {
        $this->bundle = $bundle;
    }

    public function getAll()
    {
        return $this->bundle->with(['landingCoin', 'products.supplierCoin', 'products.landingCoin', 'products.measure',
            'products.subBrand.brand', 'products.supplier'])->get();
    }

    public function find(int $id)
    {
        return $this->bundle->with(['landingCoin', 'products.supplierCoin', 'products.landingCoin', 'products.measure',
            'products.subBrand.brand', 'products.supplier'])->find($id);
    }

    public function create(array $data, Coin $landingCoin)
    {
        return DB::transaction(function () use ($data, $landingCoin) {
            $bundle = Bundle::at(
                $data['code'],
                $data['landing_cost_price'],
                $landingCoin,
                $data['retail_price'],
                $data['promotional_price']
            );

            $bundle->save();

            if (!empty($data['products'])) {
                $productIds = [];

                foreach ($data['products'] as $product) {
                    $bundle->addProduct($product);
                    $productIds[] = $product->id;
                }

                $bundle->products()->sync($productIds);
            }

            $bundle->ensureHasProducts();

            return $bundle->load(['landingCoin', 'products.supplierCoin', 'products.landingCoin', 'products.measure',
                'products.subBrand.brand', 'products.supplier',
            ]);
        });
    }

    public function update(Bundle $bundle, array $data, Coin $landingCoin)
    {
        return DB::transaction(function () use ($bundle, $data, $landingCoin) {
            Bundle::at(
                $data['code'],
                $data['landing_cost_price'],
                $landingCoin,
                $data['retail_price'],
                $data['promotional_price']
            );

            $bundle->update([
                'code' => $data['code'],
                'landing_cost_price' => $data['landing_cost_price'],
                'landing_coin_id' => $landingCoin->id,
                'retail_price' => $data['retail_price'],
                'promotional_price' => $data['promotional_price'],
            ]);

            if (!empty($data['products'])) {
                $productIds = [];

                foreach ($data['products'] as $product) {
                    $bundle->addProduct($product);
                    $productIds[] = $product->id;
                }

                $bundle->products()->sync($productIds);
            }

            $bundle->ensureHasProducts();

            return $bundle->load([
                'landingCoin', 'products.supplierCoin', 'products.landingCoin', 'products.measure',
                'products.subBrand.brand', 'products.supplier',
            ]);
        });
    }

}
