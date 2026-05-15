<?php

namespace App\Modules\StockBuy\Service;

use App\Exceptions\NotFoundException;
use App\Modules\Product\Domain\Product;
use App\Modules\Product\Service\ProductService;
use App\Modules\StockBuy\Repository\StockBuyRepository;
use Illuminate\Support\Facades\DB;

class StockBuyService
{
    private StockBuyRepository $stockBuyRepository;
    private ProductService $productService;

    public function __construct(StockBuyRepository $stockBuyRepository, ProductService $productService)
    {
        $this->stockBuyRepository = $stockBuyRepository;
        $this->productService = $productService;
    }

    public function getAllStockBuys()
    {
        return $this->stockBuyRepository->getAll();
    }

    public function getStockBuyById(int $id)
    {
        $stockBuy = $this->stockBuyRepository->find($id);

        if (!$stockBuy) {
            throw new NotFoundException('StockBuy not found.');
        }

        return $stockBuy;
    }

    public function createStockBuy(array $data)
    {
        return DB::transaction(function () use ($data) {
            $product = Product::whereRaw('LOWER(code) = ?', [strtolower($data['product']['code'])])->first();

            if (!$product) {
                throw new \RuntimeException("Product with code '{$data['product']['code']}' not found.");
            }

            $this->productService->increaseStock($product->id, $data['amount']);

            return $this->stockBuyRepository->create($data, $product);
        });
    }

    public function updateStockBuy(int $id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $stockBuy = $this->getStockBuyById($id);
            $product = $stockBuy->product;

            $oldAmount = $stockBuy->amount;
            $newAmount = $data['amount'];
            $difference = $newAmount - $oldAmount;

            if ($difference > 0) {
                $this->productService->increaseStock($product->id, $difference);
            } elseif ($difference < 0) {
                $this->productService->decreaseStock($product->id, abs($difference));
            }

            return $this->stockBuyRepository->update($stockBuy, $data);
        });
    }

    public function deleteStockBuy(int $id): void
    {
        DB::transaction(function () use ($id) {
            $stockBuy = $this->getStockBuyById($id);

            // Reverse the stock increase that was applied when this purchase was created
            $this->productService->decreaseStock($stockBuy->product_id, $stockBuy->amount);

            $this->stockBuyRepository->delete($stockBuy);
        });
    }
}
