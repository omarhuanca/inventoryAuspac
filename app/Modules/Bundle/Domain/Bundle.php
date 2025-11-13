<?php

namespace App\Modules\Bundle\Domain;

use App\Modules\Coin\Domain\Coin;
use App\Modules\Product\Domain\Product;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Bundle",
 *     type="object",
 *     title="Bundle",
 *     description="Represents a product bundle with pricing, coin and related products.",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="code", type="string", example="B001"),
 *     @OA\Property(property="landing_cost_price", type="number", format="float", example=300.00),
 *     @OA\Property(
 *         property="landing_coin",
 *         ref="#/components/schemas/Coin"
 *     ),
 *     @OA\Property(property="retail_price", type="number", format="float", example=400.00),
 *     @OA\Property(property="promotional_price", type="number", format="float", example=350.00),
 *     @OA\Property(
 *         property="products",
 *         type="array",
 *         description="List of products included in the bundle.",
 *         @OA\Items(ref="#/components/schemas/Product")
 *     )
 * )
 */
class Bundle extends Model
{
    protected $table = 'bundle';

    protected $fillable = [
        'code',
        'landing_cost_price',
        'landing_coin_id',
        'retail_price',
        'promotional_price',
    ];

    private array $products = [];

    public static $codeEmpty = 'Bundle code cannot be empty.';

    public static $codeLength = 'Bundle code must be between 2 and 50 characters long.';
    public static $codeInvalid = 'Bundle code contains invalid characters.';
    public static $landingCostInvalid = 'Landing cost must be a non-negative number.';
    public static $retailInvalid = 'Retail price must be a non-negative number.';
    public static $promotionalNegative = 'Promotional price must be a non-negative number.';
    public static $promotionalInvalid = 'Promotional price must be less than retail price.';
    public static $productsMin = 'A bundle must contain at least two products.';
    public static $productsDuplicate = 'Duplicate product in bundle is not allowed.';

    public static function at(string $code, float $landingCost, Coin $landingCoin, float $retailPrice,
                              float $promotionalPrice, array $products)
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

        if (!is_numeric($landingCost) || $landingCost < 0) {
            throw new \RuntimeException(self::$landingCostInvalid);
        }

        if (!is_numeric($retailPrice) || $retailPrice < 0) {
            throw new \RuntimeException(self::$retailInvalid);
        }

        if (!is_numeric($promotionalPrice) || $promotionalPrice < 0) {
            throw new \RuntimeException(self::$promotionalNegative);
        }

        if ($promotionalPrice >= $retailPrice) {
            throw new \RuntimeException(self::$promotionalInvalid);
        }

        if (count($products) < 2) {
            throw new \RuntimeException(self::$productsMin);
        }

        $codes = array_map(fn($p) => $p->code, $products);
        if(count($codes) !== count(array_unique($codes))) {
            throw new \RuntimeException(self::$productsDuplicate);
        }

        $bundle = new Bundle([
            'code' => trim($code),
            'landing_cost_price' => $landingCost,
            'landing_coin_id' => $landingCoin->id,
            'retail_price' => $retailPrice,
            'promotional_price' => $promotionalPrice,
        ]);

        $bundle->products = $products;

        return $bundle;
    }

    public function getProducts()
    {
        return $this->products;
    }

    public function landingCoin()
    {
        return $this->belongsTo(Coin::class, 'landing_coin_id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'bundle_product')->withTimestamps();
    }
}
