<?php

namespace App\Models;

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
            throw new \RuntimeException('Brand code cannot be empty.');
        }

        if (mb_strlen($code) < 2 || mb_strlen($code) > 50) {
            throw new \RuntimeException('Brand code must be between 2 and 50 characters long.');
        }

        if (!preg_match('/^[A-Za-z0-9_-]+$/', $code)) {
            throw new \RuntimeException('Brand code contains invalid characters. Allowed: letters, numbers, hyphen and underscore');
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
