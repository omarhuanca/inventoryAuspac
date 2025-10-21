<?php

namespace App\Models;

use App\Constants\SubBrandMessages;
use Illuminate\Database\Eloquent\Model;

class SubBrand extends Model
{
    protected $fillable = ['code', 'brand_id'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    public static function at(string $code, ?Brand $brand)
    {
        $code = trim($code);

        if ($code === '') {
            throw new \RuntimeException(SubBrandMessages::CODE_EMPTY);
        }

        if (mb_strlen($code) < 2 || mb_strlen($code) > 50) {
            throw new \RuntimeException(SubBrandMessages::CODE_LENGTH);
        }

        if (!preg_match('/^[A-Za-z0-9_-]+$/', $code)) {
            throw new \RuntimeException(SubBrandMessages::CODE_INVALID);
        }

        if (!$brand instanceof Brand) {
            throw new \RuntimeException(SubBrandMessages::BRAND_INVALID);
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
