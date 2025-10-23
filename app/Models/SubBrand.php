<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="SubBrand",
 *     type="object",
 *     title="SubBrand",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="code", type="string", example="ACME_ECO"),
 *     @OA\Property(
 *           property="brand",
 *           ref="#/components/schemas/Brand"
 *       ),
 * )
 */
class SubBrand extends Model
{
    protected $table = 'sub_brand';
    protected $fillable = ['code', 'brand_id'];

    public static $codeEmpty = 'SubBrand code cannot be empty.';
    public static $codeLength = 'SubBrand code must be between 2 and 50 characters long.';
    public static $codeInvalid = 'SubBrand code contains invalid characters. Allowed: letters, numbers, hyphen and underscore.';
    public static $brandInvalid = 'SubBrand must be associated with a valid Brand instance.';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    public static function at(string $code, ?Brand $brand)
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

        if (!$brand instanceof Brand) {
            throw new \RuntimeException(self::$brandInvalid);
        }

        return new SubBrand([
            'code' => trim($code),
            'brand_id' => $brand->id,
        ]);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
}
