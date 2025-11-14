<?php

namespace App\Modules\StockReferral\Domain;

use App\Modules\Product\Domain\Product;
use App\Modules\StockBuy\Domain\StockTransaction;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="StockReferral",
 *     type="object",
 *     title="StockReferral",
 *     description="Represents a stock exit record that decreases the stock of a product.",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *
 *     @OA\Property(
 *         property="product",
 *         ref="#/components/schemas/Product",
 *         description="Product associated with this stock referral."
 *     ),
 *
 *     @OA\Property(
 *         property="amount",
 *         type="integer",
 *         example=5,
 *         description="Quantity of units removed from stock."
 *     ),
 *
 *     @OA\Property(
 *         property="date",
 *         type="string",
 *         format="date",
 *         example="2025-11-05",
 *         description="Date when the stock exit occurred."
 *     )
 * )
 */
class StockReferral extends StockTransaction
{
    protected $table = 'stock_referral';
    public static $invalidAmount = 'The amount must not be zero or less than zero.';
    public static $invalidDate = 'Date must be a valid date.';

    public static function at(Product $product, int $amount, string $date)
    {
        if (!is_numeric($amount) || $amount <= 0) {
            throw new \RuntimeException(self::$invalidAmount);
        }

        if (!\DateTime::createFromFormat('Y-m-d', $date) || date('Y-m-d', strtotime($date)) !== $date) {
            throw new \RuntimeException(self::$invalidDate);
        }

        return new self([
            'product_id' => $product->id,
            'amount' => $amount,
            'date' => $date,
        ]);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
