<?php

namespace App\Modules\StockBuy\Domain;

use App\Modules\Product\Domain\Product;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="StockBuy",
 *     type="object",
 *     title="StockBuy",
 *     description="Represents a purchase record that increases the stock of a product.",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(
 *         property="product",
 *         ref="#/components/schemas/Product",
 *         description="Product associated with this stock buy."
 *     ),
 *     @OA\Property(property="amount", type="integer", example=10, description="Quantity of units purchased."),
 *     @OA\Property(property="date", type="string", format="date", example="2025-11-05", description="Date when the purchase occurred."),
 *     @OA\Property(property="description", type="string", example="Purchase #001", description="Short description or reference for this purchase.")
 * )
 */
class StockBuy extends StockTransaction
{
    protected $table = 'stock_buy';
    public function __construct(array $attributes = [])
    {
        $this->fillable = array_merge($this->fillable, ['description']);
        parent::__construct($attributes);
    }

    public static $invalidAmount = 'The amount must be greater than zero and not negative.';
    public static $invalidDate = 'Date must be a valid date.';
    public static $descriptionTooLong = 'Description must not exceed 255 characters.';

    public static function at(Product $product, int $amount, string $date, string $description)
    {
        if (!is_numeric($amount) || $amount <= 0) {
            throw new \RuntimeException(self::$invalidAmount);
        }

        if (!\DateTime::createFromFormat('Y-m-d', $date) || date('Y-m-d', strtotime($date)) !== $date) {
            throw new \RuntimeException(self::$invalidDate);
        }

        if ($description && mb_strlen($description) > 255) {
            throw new \RuntimeException(self::$descriptionTooLong);
        }

        return new StockBuy([
            'product_id' => $product->id,
            'amount' => $amount,
            'date' => $date,
            'description' => trim($description),
        ]);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
