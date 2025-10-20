<?php

namespace App\Models;

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
            throw new \RuntimeException('SubBrand code cannot be empty.');
        }

        if (mb_strlen($code) < 2 || mb_strlen($code) > 50) {
            throw new \RuntimeException('SubBrand code must be between 2 and 50 characters long.');
        }

        if (!preg_match('/^[A-Za-z0-9_-]+$/', $code)) {
            throw new \RuntimeException('SubBrand code contains invalid characters. Allowed: letters, numbers, hyphen and underscore');
        }

        if (!$brand instanceof Brand) {
            throw new \RuntimeException('SubBrand must be associated with a valid Brand instance.');
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
