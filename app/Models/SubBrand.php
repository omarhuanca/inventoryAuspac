<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubBrand extends Model
{
    protected $fillable = ['code', 'brand_id'];

    public const CODE_EMPTY = 'SubBrand code cannot be empty.';
    public const CODE_LENGTH = 'SubBrand code must be between 2 and 50 characters long.';
    public const CODE_INVALID = 'SubBrand code contains invalid characters. Allowed: letters, numbers, hyphen and underscore.';
    public const BRAND_INVALID = 'SubBrand must be associated with a valid Brand instance.';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    public static function at(string $code, ?Brand $brand)
    {
        $code = trim($code);

        if ($code === '') {
            throw new \RuntimeException(SubBrand::CODE_EMPTY);
        }

        if (mb_strlen($code) < 2 || mb_strlen($code) > 50) {
            throw new \RuntimeException(SubBrand::CODE_LENGTH);
        }

        if (!preg_match('/^[A-Za-z0-9_-]+$/', $code)) {
            throw new \RuntimeException(SubBrand::CODE_INVALID);
        }

        if (!$brand instanceof Brand) {
            throw new \RuntimeException(SubBrand::BRAND_INVALID);
        }

        return new SubBrand([
            'code' => $code,
            'brand_id' => $brand->id,
        ]);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
}
