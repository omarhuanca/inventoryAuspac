<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
    public static $noProducts = 'A bundle must contain at least two products.';

    public static function at(string $code, float $landingCost, Coin $landingCoin, float $retailPrice, float $promotionalPrice)
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

        return new Bundle([
            'code' => trim($code),
            'landing_cost_price' => $landingCost,
            'landing_coin_id' => $landingCoin->id,
            'retail_price' => $retailPrice,
            'promotional_price' => $promotionalPrice,
        ]);
    }

    public function addProduct(Product $product)
    {
        foreach ($this->products as $existing) {
            if ($existing->code === $product->code) {
                throw new \RuntimeException('Duplicate product in bundle is not allowed.');
            }
        }

        $this->products[] = $product;
    }

    public function ensureHasProducts()
    {
        if (!$this->exists) {
            $count = count($this->products ?? []);
        } else {
            $count = $this->products()->count();
        }

        if ($count < 2) {
            throw new \RuntimeException(self::$noProducts);
        }
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
