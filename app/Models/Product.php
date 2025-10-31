<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Product",
 *     type="object",
 *     title="Product",
 *     description="Represents a product with its pricing, dimensions, and associations.",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="code", type="string", example="P001"),
 *     @OA\Property(property="supplier_cost_price", type="number", format="float", example=100.50),
 *     @OA\Property(
 *         property="supplier_coin",
 *         ref="#/components/schemas/Coin"
 *     ),
 *     @OA\Property(property="landing_cost_price", type="number", format="float", example=120.75),
 *     @OA\Property(
 *         property="landing_coin",
 *         ref="#/components/schemas/Coin"
 *     ),
 *     @OA\Property(property="retail_price", type="number", format="float", example=200.00),
 *     @OA\Property(property="promotional_price", type="number", format="float", example=180.00),
 *     @OA\Property(property="stock", type="integer", example=50),
 *     @OA\Property(
 *         property="measure",
 *         ref="#/components/schemas/Measure"
 *     ),
 *     @OA\Property(property="serial_tracking", type="string", example="SER001"),
 *     @OA\Property(property="dimension_size", type="string", example="10x10x10"),
 *     @OA\Property(property="dimension_weight", type="integer", example=5),
 *     @OA\Property(
 *         property="sub_brand",
 *         ref="#/components/schemas/SubBrand"
 *     ),
 *     @OA\Property(
 *         property="supplier",
 *         ref="#/components/schemas/Supplier"
 *     ),
 * )
 */
class Product extends Model
{
    protected $table = 'product';

    protected $fillable = [
        'code',
        'supplier_cost_price',
        'supplier_coin_id',
        'landing_cost_price',
        'landing_coin_id',
        'retail_price',
        'promotional_price',
        'stock',
        'measure_id',
        'serial_tracking',
        'dimension_size',
        'dimension_weight',
        'sub_brand_id',
        'supplier_id',
    ];

    public static $codeEmpty = 'Product code cannot be empty.';
    public static $codeLength = 'Product code must be between 2 and 50 characters long.';
    public static $codeInvalid = 'Product code contains invalid characters. Allowed: letters, numbers, hyphen and underscore.';
    public static $supplierCostPriceInvalid = 'Supplier cost must be a non-negative number.';
    public static $landingCostPriceInvalid = 'Landing cost must be a non-negative number.';
    public static $retailInvalid = 'Retail price must be a non-negative number.';
    public static $promotionalInvalid = 'Promotional price must be less than retail price.';
    public static $stockInvalid = 'Stock must be a non-negative integer.';
    public static $dimensionSizeEmpty = 'Dimension size cannot be empty.';
    public static $dimensionInvalid = 'Dimension size must not exceed 50 characters.';
    public static $weightInvalid = 'Dimension weight must be a non-negative integer.';
    public static $serialTrackingEmpty = 'Serial tracking cannot be empty.';
    public static $serialInvalid = 'Serial tracking code must not exceed 100 characters.';

    public static function at(string $code, float $supplierCostPrice, Coin $supplierCoin,
                              float $landingCostPrice, Coin $landingCoin, float $retailPrice, float $promotionalPrice,
                              int $stock, Measure $measure, string $serialTracking, string $dimensionSize, int $dimensionWeight,
                              SubBrand $subBrand, Supplier $supplier)
    {
        if ($code === '') {
            throw new \RuntimeException(self::$codeEmpty);
        }

        if (mb_strlen($code) < 2 || mb_strlen($code) > 50) {
            throw new \RuntimeException(self::$codeLength);
        }

        if (!preg_match('/^[A-Za-z0-9_-]+$/', $code)) {
            throw new \RuntimeException(self::$codeInvalid);
        }

        if (!is_numeric($supplierCostPrice) || $supplierCostPrice < 0) {
            throw new \RuntimeException(self::$supplierCostPriceInvalid);
        }

        if (!is_numeric($landingCostPrice) || $landingCostPrice < 0) {
            throw new \RuntimeException(self::$landingCostPriceInvalid);
        }

        if (!is_numeric($retailPrice) || $retailPrice < 0) {
            throw new \RuntimeException(self::$retailInvalid);
        }

        if (!is_numeric($promotionalPrice) || $promotionalPrice >= $retailPrice) {
            throw new \RuntimeException(self::$promotionalInvalid);
        }

        if ($stock < 0) {
            throw new \RuntimeException(self::$stockInvalid);
        }

        if ($dimensionSize === '') {
            throw new \RuntimeException(self::$dimensionSizeEmpty);
        }

        if (mb_strlen($dimensionSize) > 50) {
            throw new \RuntimeException(self::$dimensionInvalid);
        }

        if ($dimensionWeight < 0) {
            throw new \RuntimeException(self::$weightInvalid);
        }

        if ($serialTracking === '') {
            throw new \RuntimeException(self::$serialTrackingEmpty);
        }

        if (mb_strlen($serialTracking) > 100) {
            throw new \RuntimeException(self::$serialInvalid);
        }

        return new Product([
            'code' => trim($code),
            'supplier_cost_price' => $supplierCostPrice,
            'supplier_coin_id' => $supplierCoin->id,
            'landing_cost_price' => $landingCostPrice,
            'landing_coin_id' => $landingCoin->id,
            'retail_price' => $retailPrice,
            'promotional_price' => $promotionalPrice,
            'stock' => $stock,
            'measure_id' => $measure->id,
            'serial_tracking' => trim($serialTracking),
            'dimension_size' => trim($dimensionSize),
            'dimension_weight' => $dimensionWeight,
            'sub_brand_id' => $subBrand->id,
            'supplier_id' => $supplier->id,
        ]);
    }

    public function supplierCoin()
    {
        return $this->belongsTo(Coin::class, 'supplier_coin_id');
    }
    public function landingCoin()
    {
        return $this->belongsTo(Coin::class, 'landing_coin_id');
    }
    public function measure()
    {
        return $this->belongsTo(Measure::class);
    }
    public function subBrand()
    {
        return $this->belongsTo(SubBrand::class);
    }
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function bundles()
    {
        return $this->belongsToMany(Bundle::class, 'bundle_product')->withTimestamps();
    }
}
