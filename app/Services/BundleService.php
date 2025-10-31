<?php

namespace App\Services;

use App\Exceptions\NotFoundException;
use App\Models\Bundle;
use App\Models\Product;
use App\Repositories\BundleRepository;

class BundleService
{
    private BundleRepository $bundleRepository;
    private CoinService $coinService;

    public function __construct(BundleRepository $bundleRepository, CoinService $coinService)
    {
        $this->bundleRepository = $bundleRepository;
        $this->coinService = $coinService;
    }

    public function getAllBundles()
    {
        return $this->bundleRepository->getAll();
    }

    public function getBundleById(int $id)
    {
        $bundle = $this->bundleRepository->find($id);

        if (!$bundle) {
            throw new NotFoundException('Bundle not found.');
        }

        return $bundle;
    }

    public function createBundle(array $data)
    {
        $landingCoin = $this->coinService->getCoinById($data['landing_coin']['id']);

        $exists = Bundle::whereRaw('LOWER(code) = ?', [strtolower($data['code'])])->exists();
        if ($exists) {
            throw new \RuntimeException('Bundle code already exists.');
        }

        $products = [];
        if (!empty($data['products'])) {
            foreach ($data['products'] as $productData) {
                $product = Product::where('code', $productData['code'])->first();

                if (!$product) {
                    throw new \RuntimeException("Product with code '{$productData['code']}' not found.");
                }

                $products[] = $product;
            }
        }

        $data['products'] = $products;

        return $this->bundleRepository->create($data, $landingCoin);
    }

    public function updateBundle(int $id, array $data)
    {
        $bundle = $this->getBundleById($id);
        $landingCoin = $this->coinService->getCoinById($data['landing_coin']['id']);

        $exists = Bundle::whereRaw('LOWER(code) = ?', [strtolower($data['code'])])
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            throw new \RuntimeException('Bundle code already exists.');
        }

        $products = [];
        if (!empty($data['products'])) {
            foreach ($data['products'] as $productData) {
                $product = Product::where('code', $productData['code'])->first();

                if (!$product) {
                    throw new \RuntimeException("Product with code '{$productData['code']}' not found.");
                }

                $products[] = $product;
            }
        }

        $data['products'] = $products;

        return $this->bundleRepository->update($bundle, $data, $landingCoin);
    }

}
