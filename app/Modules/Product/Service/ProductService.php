<?php

namespace App\Modules\Product\Service;

use App\Exceptions\NotFoundException;
use App\Modules\Coin\Service\CoinService;
use App\Modules\Measure\Service\MeasureService;
use App\Modules\Product\Domain\Product;
use App\Modules\Product\Repository\ProductRepository;
use App\Modules\SubBrand\Service\SubBrandService;
use App\Modules\Supplier\Service\SupplierService;

class ProductService
{
    private ProductRepository $productRepository;
    private CoinService $coinService;
    private MeasureService $measureService;
    private SubBrandService $subBrandService;
    private SupplierService $supplierService;

    public function __construct(
        ProductRepository $productRepository,
        CoinService $coinService,
        MeasureService $measureService,
        SubBrandService $subBrandService,
        SupplierService $supplierService
    ) {
        $this->productRepository = $productRepository;
        $this->coinService = $coinService;
        $this->measureService = $measureService;
        $this->subBrandService = $subBrandService;
        $this->supplierService = $supplierService;
    }

    public function getAllProducts()
    {
        return $this->productRepository->getAll();
    }

    public function getProductById(int $id)
    {
        $product = $this->productRepository->find($id);

        if (!$product) {
            throw new NotFoundException('Product not found.');
        }

        return $product;
    }

    public function createProduct(array $data)
    {
        $supplierCoin = $this->coinService->getCoinById($data['supplier_coin']['id']);
        $landingCoin = $this->coinService->getCoinById($data['landing_coin']['id']);
        $measure = $this->measureService->getMeasureById($data['measure']['id']);
        $subBrand = $this->subBrandService->getSubBrandById($data['sub_brand']['id']);
        $supplier = $this->supplierService->getSupplierById($data['supplier']['id']);

        $exists = Product::whereRaw('LOWER(code) = ?', [strtolower($data['code'])])->exists();
        if ($exists) {
            throw new \RuntimeException('Product code already exists.');
        }

        return $this->productRepository->create(
            $data, $supplierCoin, $landingCoin,
            $measure, $subBrand, $supplier
        );
    }

    public function updateProduct(int $id, array $data)
    {
        $product = $this->getProductById($id);

        $supplierCoin = $this->coinService->getCoinById($data['supplier_coin']['id']);
        $landingCoin = $this->coinService->getCoinById($data['landing_coin']['id']);
        $measure = $this->measureService->getMeasureById($data['measure']['id']);
        $subBrand = $this->subBrandService->getSubBrandById($data['sub_brand']['id']);
        $supplier = $this->supplierService->getSupplierById($data['supplier']['id']);

        $exists = Product::whereRaw('LOWER(code) = ?', [strtolower($data['code'])])
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            throw new \RuntimeException('Product code already exists.');
        }

        return $this->productRepository->update($product, $data, $supplierCoin, $landingCoin, $measure, $subBrand, $supplier);
    }

    public function increaseStock(int $productId, float $amount)
    {
        $this->validateAmount($amount);

        $product = $this->getProductById($productId);

        return $this->productRepository->incrementStock($product, $amount);
    }

    public function decreaseStock(int $productId, float $amount)
    {
        $this->validateAmount($amount);

        $product = $this->getProductById($productId);

        if ($product->stock < $amount) {
            throw new \RuntimeException('Insufficient stock.');
        }

        return $this->productRepository->decrementStock($product, $amount);
    }

    public function validateAmount(float $amount)
    {
        if ($amount <= 0) {
            throw new \RuntimeException('The amount must not be zero or less than zero.');
        }
    }
}
