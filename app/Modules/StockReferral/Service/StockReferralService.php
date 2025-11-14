<?php

namespace App\Modules\StockReferral\Service;

use App\Modules\Product\Service\ProductService;
use App\Modules\StockReferral\Repository\StockReferralRepository;
use App\Modules\Product\Domain\Product;
use App\Exceptions\NotFoundException;
use Illuminate\Support\Facades\DB;

class StockReferralService
{
    private StockReferralRepository $stockReferralRepository;
    private ProductService $productService;

    public function __construct(
        StockReferralRepository $stockReferralRepository,
        ProductService $productService
    ) {
        $this->stockReferralRepository = $stockReferralRepository;
        $this->productService = $productService;
    }

    public function getAllStockReferrals()
    {
        return $this->stockReferralRepository->getAll();
    }

    public function getStockReferralById(int $id)
    {
        $stockReferral = $this->stockReferralRepository->find($id);

        if (!$stockReferral) {
            throw new NotFoundException('StockReferral not found.');
        }

        return $stockReferral;
    }

    public function createStockReferral(array $data)
    {
        return DB::transaction(function () use ($data) {
            $product = Product::whereRaw('LOWER(code) = ?', [strtolower($data['product']['code'])])->first();

            if (!$product) {
                throw new \RuntimeException("Product with code '{$data['product']['code']}' not found.");
            }

            $this->productService->decreaseStock($product->id, $data['amount']);

            return $this->stockReferralRepository->create($data, $product);
        });
    }

    public function updateStockReferral(int $id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $stockReferral = $this->getStockReferralById($id);
            $product = $stockReferral->product;

            $oldAmount = $stockReferral->amount;
            $newAmount = $data['amount'];
            $difference = $newAmount - $oldAmount;

            if ($difference > 0) {
                $this->productService->decreaseStock($product->id, $difference);
            }
            elseif ($difference < 0) {
                $this->productService->increaseStock($product->id, abs($difference));
            }

            return $this->stockReferralRepository->update($stockReferral, $data);
        });
    }
}
