<?php

namespace App\Models;

use App\Constants\BrandMessages;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    protected $fillable = ['code'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    public static function at(string $code): Brand
    {
        $code = trim($code);

        if ($code === '')
        {
            throw new \RuntimeException(BrandMessages::CODE_EMPTY);
        }

        if (mb_strlen($code) < 2 || mb_strlen($code) > 50) {
            throw new \RuntimeException(BrandMessages::CODE_LENGTH);
        }

        if (!preg_match('/^[A-Za-z0-9_-]+$/', $code)) {
            throw new \RuntimeException(BrandMessages::CODE_INVALID_CHARS);
        }

        return new Brand([
            'code' => $code,
        ]);
    }

    public function subBrands()
    {
        return $this->hasMany(SubBrand::class);
    }
}
