<?php

namespace App\Modules\Bundle\Service;

use App\Exceptions\NotFoundException;
use App\Modules\Bundle\Domain\Bundle;
use App\Modules\Bundle\Repository\BundleRepository;
use App\Modules\Coin\Service\CoinService;
use App\Modules\Product\Domain\Product;

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
            $productCodes = array_column($data['products'], 'code');
            $foundProducts = Product::whereIn('code', $productCodes)->get()->keyBy('code');

            foreach ($productCodes as $code) {
                if (!isset($foundProducts[$code])) {
                    throw new \RuntimeException("Product with code '{$code}' not found.");
                }
                $products[] = $foundProducts[$code];
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
            $productCodes = array_column($data['products'], 'code');
            $foundProducts = Product::whereIn('code', $productCodes)->get()->keyBy('code');

            foreach ($productCodes as $code) {
                if (!isset($foundProducts[$code])) {
                    throw new \RuntimeException("Product with code '{$code}' not found.");
                }
                $products[] = $foundProducts[$code];
            }
        }

        $data['products'] = $products;

        return $this->bundleRepository->update($bundle, $data, $landingCoin);
    }

}
