<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="BundleProduct",
 *     type="object",
 *     title="BundleProduct",
 *     description="Represents the pivot relation between Bundles and Products.",
 *     @OA\Property(property="bundle_id", type="integer", example=1),
 *     @OA\Property(property="product_id", type="integer", example=2)
 * )
 */
class BundleProduct extends Model
{
    protected $table = 'bundle_product';
    protected $fillable = ['bundle_id', 'product_id'];
    public static string $bundleRequired = 'A valid bundle must be provided.';
    public static string $productRequired = 'A valid product must be provided.';

    public static function at(Bundle $bundle, Product $product)
    {
        if (!$bundle) {
            throw new \RuntimeException(self::$bundleRequired);
        }

        if (!$product) {
            throw new \RuntimeException(self::$productRequired);
        }

        return new BundleProduct([
            'bundle_id' => $bundle->id,
            'product_id' => $product->id,
        ]);
    }

    public function bundle()
    {
        return $this->belongsTo(Bundle::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
